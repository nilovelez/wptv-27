/* Focused landing checks; preview injection is browser-only, never saved to WordPress. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require(process.env.WPTV_PLAYWRIGHT_PATH || 'playwright');
const root = path.resolve(__dirname, '../../../../..');
const expected = JSON.parse(fs.readFileSync(path.join(root, 'wp-content/wptv-wordcamps-expected.json'), 'utf8'));
const preview = fs.readFileSync(path.join(root, 'wp-content/wptv-wordcamp-preview.html'), 'utf8');
const base = process.env.WPTV_TEST_URL || 'http://localhost:8882';
let checks = 0;
const check = (value, message) => { assert.ok(value, message); checks++; };
const equal = (value, wanted, message) => { assert.deepEqual(value, wanted, message); checks++; };
(async () => {
	const browser = await chromium.launch({ headless: true });
	try {
		const page = await browser.newPage();
		for (const width of [320, 782, 1440]) {
			await page.setViewportSize({ width, height: 900 });
			for (const number of [1, 2]) {
				const url = `${base}/category/wordcamptv/${number > 1 ? `page/${number}/` : ''}`;
				const response = await page.goto(url, { waitUntil: 'domcontentloaded' });
				equal(response.status(), 200, `Page ${number} at ${width}: status`);
				equal(await page.locator('main h1').count(), 1, 'One archive h1');
				const selector = number === 1 ? '.wptv-taxonomy__spotlight .wp-block-post' : 'main > .wptv-listing__query .wp-block-post';
				const ids = await page.locator(selector).evaluateAll(nodes => nodes.map(n => Number(n.className.match(/\bpost-(\d+)\b/)[1])));
				equal(ids, number === 1 ? expected.pages[number].slice(0, 6) : expected.pages[number], 'Landing previews the first six; existing later pages retain all queried IDs');
				equal(await page.locator('.wptv-taxonomy__spotlight').count(), number === 1 ? 1 : 0, 'Spotlight appears only on the landing');
				check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `No horizontal overflow at ${width}`);
				if (number === 2) {
					equal(await page.locator('.wptv-wordcamps__recent, .wptv-wordcamps__directory').count(), 0, 'Page two does not repeat landing sections');
					continue;
				}
				equal(await page.locator('.wptv-taxonomy__lead > .wp-block-post').count(), 1, 'One lead video');
				equal(await page.locator('.wptv-taxonomy__secondary > .wp-block-post').count(), 5, 'Five secondary videos');
				equal(await page.locator('main > .wptv-listing__query').count(), 0, 'No 22-video grid or its pagination on the landing');
				equal(await page.getByRole('heading', { name: 'Recent videos', exact: true }).count(), 0, 'No Recent videos heading');
				equal(await page.locator('.wptv-wordcamps__recent > h2').textContent(), 'Latest WordCamps', 'Correct event-section label');
				const years = await page.locator('.wptv-wordcamps__year > h3').allTextContents();
				equal(years, Object.keys(expected.groups).sort((a, b) => b - a), 'Directory years descend');
				const actual = await page.locator('.wptv-wordcamps__directory-list > li').evaluateAll(nodes => nodes.map(n => Number(n.className.match(/\bterm-(\d+)\b/)[1])));
				equal(actual.sort((a, b) => a - b), Object.values(expected.groups).flat().sort((a, b) => a - b), 'Directory contains exactly the qualifying category-associated events');
				equal(new Set(actual).size, actual.length, 'No duplicate events');
				const columns = await page.locator('.wptv-wordcamps__directory-list').first().evaluate(n => getComputedStyle(n).gridTemplateColumns.split(' ').length);
				equal(columns, width >= 960 ? 3 : width >= 600 ? 2 : 1, 'Directory responsive column count');
				check(!(await page.locator('main').textContent()).match(/WPTV_EVENT_NAME|WPTV_YEAR/), 'No unresolved prototype labels');
			}
		}
		const feed = await page.request.get(`${base}/category/wordcamptv/feed/`);
		equal(feed.status(), 200, 'Existing category feed still responds');
		check(!(await feed.text()).includes('wptv-wordcamps__directory'), 'Landing sections never enter the feed');

		// Existing event videos populate an isolated initial-response preview. No
		// recording dates/content/transients are fabricated in the live page.
		const fixture = await browser.newPage({ viewport: { width: 1440, height: 900 } });
		await fixture.route(`${base}/category/wordcamptv/`, async route => {
			const response = await route.fetch();
			const html = await response.text();
			const target = /(<div class="[^"]*\bwptv-wordcamps__recent-content\b[^"]*">)[\s\S]*?(<\/div>)/;
			check(target.test(html), 'Isolated preview has a target group');
			await route.fulfill({ response, body: html.replace(target, (_, open, close) => open + preview + close) });
		});
		await fixture.goto(`${base}/category/wordcamptv/`, { waitUntil: 'domcontentloaded' });
		const cards = fixture.locator('.wptv-wordcamps__recent-content .wp-block-post');
		equal(await cards.count(), 4, 'Populated preview renders four real event videos');
		equal(await fixture.locator('.wptv-wordcamps__event h3').count(), 1, 'Event heading is h3');
		equal(await cards.locator('h4.wp-block-post-title a').count(), 4, 'Video titles are native h4 links');
		equal(await cards.locator('.wp-block-post-featured-image a, .wptv-card__speakers a').count(), 0, 'Images and speakers remain unlinked');
		equal(await cards.locator('.wp-block-post-date').count(), 4, 'Event cards retain publication dates');
		equal(await fixture.locator('.wptv-wordcamps__event .wptv-home__section-heading a').getAttribute('href'), expected.event_url, 'View all points to the native event archive');
		check(await cards.first().evaluate(n => n.classList.contains('wptv-card--clickable')), 'Existing progressive card script recognizes event cards');
		await fixture.setViewportSize({ width: 320, height: 900 });
		check(await fixture.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'Populated section has no mobile overflow');
		const href = await cards.first().locator('.wp-block-post-title a').getAttribute('href');
		await Promise.all([fixture.waitForURL(href), cards.first().locator('img').click()]);
		checks++;
		console.log(`${checks} focused WordCampTV browser checks passed; preview remained browser-only.`);
	} finally {
		await browser.close();
	}
})().catch(error => { console.error(error); process.exitCode = 1; });
