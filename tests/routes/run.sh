#!/usr/bin/env bash
#
# Requests every URL in urls.txt and records theme-independent route data.
#
# Usage:
#     tests/routes/run.sh <base-url> <run-name>
# Example:
#     tests/routes/run.sh http://localhost:8882 baseline-wptv2
#
# Writes results/<run-name>/:
#     results.tsv  one row per URL (compared by compare.sh)
#     timings.tsv  response times (informational, never compared)
#     bodies/      normalized bodies of non-HTML responses (JSON, XML, feeds)
#
# Redirects are NOT followed: the status code and Location header are recorded,
# so redirect targets can be compared exactly.
#
# Requires: bash, curl, grep, sed, sha1sum (Git Bash on Windows works).

set -u

if [ $# -lt 2 ]; then
	echo "Usage: $0 <base-url> <run-name>" >&2
	exit 1
fi

BASE_URL="${1%/}"
RUN_NAME="$2"
DIR="$(cd "$(dirname "$0")" && pwd)"
URLS="$DIR/urls.txt"
OUT="$DIR/results/$RUN_NAME"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

if [ ! -f "$URLS" ]; then
	echo "Missing $URLS. Run generate-urls.php first." >&2
	exit 1
fi

rm -rf "$OUT"
mkdir -p "$OUT/bodies"

# PHP notices logged by the mu-plugin (wp-content/wptv-route-test.log). Cleared before the run.
PHP_LOG="$DIR/../../../../wptv-route-test.log"
: > "$PHP_LOG" 2> /dev/null

printf 'label\tpath\tstatus\tlocation\tcontent_type\tx_wptv_route\tcanonical\ttitle\trel_prev\trel_next\tbody_sha1\n' > "$OUT/results.tsv"
printf 'label\tseconds\n' > "$OUT/timings.tsv"

# Base URL as it appears in JSON, with escaped slashes (http:\/\/host), as a sed pattern.
BASE_URL_JSON=$(printf '%s' "$BASE_URL" | sed 's#/#\\\\\\\\/#g')

# Makes absolute URLs on the tested site relative, so runs on different hosts compare.
relativize() {
	sed -e "s#${BASE_URL}##g" -e "s#${BASE_URL_JSON}##g"
}

# Prints the value of an attribute from the first tag that matches a pattern.
tag_attr() {
	local file="$1" pattern="$2" attr="$3"
	grep -oiE "<link[^>]*${pattern}[^>]*>" "$file" | head -n 1 |
		sed -nE "s/.*${attr}=[\"']([^\"']*)[\"'].*/\1/p"
}

total=$(grep -vc '^#' "$URLS")
n=0

while IFS=$'\t' read -r label path; do
	case "$label" in '#'* | '') continue ;; esac
	n=$((n + 1))
	printf '[%3d/%3d] %s\n' "$n" "$total" "$label" >&2

	body="$TMP/body"
	headers="$TMP/headers"
	meta=$(curl -s -o "$body" -D "$headers" --max-time 120 -H 'X-WPTV-Route-Test: 1' \
		-w '%{http_code}\t%{content_type}\t%{time_total}' "${BASE_URL}${path}")

	status=$(printf '%s' "$meta" | cut -f1)
	ctype=$(printf '%s' "$meta" | cut -f2 | sed 's/;.*//' | tr 'A-Z' 'a-z')
	seconds=$(printf '%s' "$meta" | cut -f3)

	location=$(grep -i '^location:' "$headers" | head -n 1 | sed 's/^[^:]*: *//' | tr -d '\r' | relativize)
	route=$(grep -i '^x-wptv-route:' "$headers" | head -n 1 | sed 's/^[^:]*: *//' | tr -d '\r')

	# Decide by content, not by header: legacy endpoints send JSON or plain text as text/html.
	canonical='' title='' prev='' next='' sha=''
	# Only documents that START with a doctype or <html> count (oEmbed XML contains an <html> element).
	if head -c 1024 "$body" | tr -d '\r\n' | sed -E 's/^(\xEF\xBB\xBF)?[[:space:]]*//' | grep -qiE '^(<!doctype html|<html[ >])'; then
		canonical=$(tag_attr "$body" "rel=[\"']canonical[\"']" href | relativize)
		prev=$(tag_attr "$body" "rel=[\"']prev[\"']" href | relativize)
		next=$(tag_attr "$body" "rel=[\"']next[\"']" href | relativize)
		title=$(tr -d '\r\n' < "$body" | grep -oiE '<title[^>]*>[^<]*</title>' | head -n 1 |
			sed -E 's/<[^>]+>//g; s/^[[:space:]]+|[[:space:]]+$//g')
	elif [ -s "$body" ]; then
		# Normalize: relative URLs, and strip per-request random values (oEmbed secrets, nonces).
		safe=$(printf '%s' "$label" | tr -c 'A-Za-z0-9._-' '_')
		# Note: [\]? (not \\?) matches an optional backslash portably, including Git Bash sed.
		# Non-breaking spaces become plain spaces: wptv2's wptv_widont() typography filter is
		# intentionally dropped (replaced by CSS text-wrap), see docs/decisions.md.
		relativize < "$body" |
			sed -E \
				-e 's/(data-secret=[\]?")[A-Za-z0-9]+/\1SECRET/g' \
				-e 's/(secret=)[A-Za-z0-9]+/\1SECRET/g' \
				-e 's/("secret":")[A-Za-z0-9]+/\1SECRET/g' \
				-e 's/(_wpnonce=|"nonce":")[a-f0-9]+/\1NONCE/g' \
				-e 's/&#160;|&nbsp;|[\]u00a0/ /g' \
			> "$OUT/bodies/$safe.txt"
		sha=$(sha1sum "$OUT/bodies/$safe.txt" | cut -d' ' -f1)
	fi

	printf '%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n' \
		"$label" "$path" "$status" "$location" "$ctype" "$route" "$canonical" "$title" "$prev" "$next" "$sha" \
		>> "$OUT/results.tsv"
	printf '%s\t%s\n' "$label" "$seconds" >> "$OUT/timings.tsv"
done < "$URLS"

# Keep the PHP notices of this run, with absolute paths and timestamps removed.
if [ -s "$PHP_LOG" ]; then
	sed -E 's/^\[[^]]*\] //; s#[A-Za-z]:\\[^ ]*\\wp-content\\#wp-content/#g; s#\\#/#g' "$PHP_LOG" | sort | uniq -c | sort -rn > "$OUT/php-notices.txt"
	echo "PHP notices: $(wc -l < "$OUT/php-notices.txt") distinct (see $OUT/php-notices.txt)" >&2
else
	echo "PHP notices: none" >&2
fi

echo "Done: $n URLs. Results in $OUT" >&2
