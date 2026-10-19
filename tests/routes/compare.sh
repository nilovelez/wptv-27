#!/usr/bin/env bash
#
# Compares two route test runs and lists every difference.
#
# Usage:
#     tests/routes/compare.sh <baseline-run> <current-run>
# Example:
#     tests/routes/compare.sh baseline-wptv2 wptv-27
#
# Exit code: 0 when the runs match, 1 when there are differences.
#
# Compared per URL: status, Location, content type, X-WPTV-Route (main query data),
# canonical, title, rel prev/next, and the normalized body of non-HTML responses.
# Timings are never compared.

set -u

if [ $# -lt 2 ]; then
	echo "Usage: $0 <baseline-run> <current-run>" >&2
	exit 1
fi

DIR="$(cd "$(dirname "$0")" && pwd)"
BASE="$DIR/results/$1"
CURR="$DIR/results/$2"

for run in "$BASE" "$CURR"; do
	if [ ! -f "$run/results.tsv" ]; then
		echo "Missing $run/results.tsv" >&2
		exit 1
	fi
done

# Compare row by row, keyed by label. awk keeps empty tab-separated fields intact.
report=$(awk -F'\t' -v curr_name="$2" '
	FNR == 1 { for (i = 1; i <= NF; i++) col[i] = $i; next }
	NR == FNR { base[$1] = $0; order[++n] = $1; next }
	{ curr[$1] = $0 }
	END {
		diffs = 0
		for (k = 1; k <= n; k++) {
			label = order[k]
			if (!(label in curr)) { printf "MISSING  %s (not in %s)\n", label, curr_name; diffs++; continue }
			nb = split(base[label], b, "\t"); nc = split(curr[label], c, "\t")
			for (i = 3; i <= 11; i++) {
				if (b[i] != c[i]) {
					printf "DIFF     %s [%s]\n         - %s\n         + %s\n", label, col[i], b[i], c[i]
					diffs++
				}
			}
		}
		print "__DIFFS__ " diffs
	}
' "$BASE/results.tsv" "$CURR/results.tsv")

printf '%s\n' "$report" | grep -v '^__DIFFS__ '
differences=$(printf '%s\n' "$report" | sed -n 's/^__DIFFS__ //p')

# Body diffs, for quick inspection of endpoint payload changes.
if [ -d "$BASE/bodies" ] && [ -d "$CURR/bodies" ]; then
	if ! diff -rq "$BASE/bodies" "$CURR/bodies" > /dev/null; then
		echo
		echo "Body differences (diff -u):"
		diff -ru "$BASE/bodies" "$CURR/bodies" | head -n 200
	fi
fi

echo
if [ "$differences" -eq 0 ]; then
	echo "OK: $2 matches $1."
	exit 0
fi
echo "FAIL: $differences difference(s) between $1 and $2."
exit 1
