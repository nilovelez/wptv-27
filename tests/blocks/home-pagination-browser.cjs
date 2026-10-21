/* Focused checks for native Query pagination; no content or options are changed. */
const assert = require('node:assert/strict');
const { chromium } = require(process.env.WPTV_PLAYWRIGHT_PATH || 'playwright');
const base = process.env.WPTV_TEST_URL || 'http://localhost:8882';
const region = '.wptv-home__recent';
const cards = `${region} .wp-block-post-template > .wp-block-post`;
const ids = (page) => page.locator(cards).evaluateAll((nodes) => nodes.map((node) => [...node.classList].find((name) => /^post-\d+$/.test(name))));

(async () => {
	const browser = await chromium.launch({ headless: true });
	try {
		async function context(options = {}) {
			const ctx = await browser.newContext(options);
			await ctx.route('**/*', (route) => new URL(route.request().url()).origin === new URL(base).origin ? route.continue() : route.abort());
			return ctx;
		}
		async function open(page, path = '/') {
			const response = await page.goto(base + path, { waitUntil: 'domcontentloaded', timeout: 60000 });
			assert.equal(response.status(), 200);
		}
		async function ready(page) {
			const src = await page.locator('script[type="module"][id*="block-library/query/view"]').getAttribute('src');
			assert.ok(src, 'Native Query module is enqueued');
			await page.evaluate(async (url) => { await import(url); }, src);
			await page.evaluate(() => {
				window.__paginationDocument = true;
				window.__paginationHero = document.querySelector('.wptv-home__hero');
			});
		}
		async function waitForIds(page, expected) {
			await page.waitForFunction(({ selector, expected }) => JSON.stringify([...document.querySelectorAll(selector)].map((node) => [...node.classList].find((name) => /^post-\d+$/.test(name)))) === JSON.stringify(expected), { selector: cards, expected });
		}

		const plain = await context({ javaScriptEnabled: false });
		const direct = await plain.newPage();
		await open(direct, '/page/2/');
		const second = await ids(direct);
		assert.ok(second.length > 0, 'Direct page two works without JavaScript');
		assert.ok(await direct.locator(`${region} .wp-block-query-pagination-previous`).getAttribute('href'));
		await plain.close();

		const enhanced = await context();
		const page = await enhanced.newPage();
		await open(page);
		await ready(page);
		const first = await ids(page);
		assert.equal(first.length, second.length, 'Existing page size is preserved');
		assert.notDeepEqual(first, second);
		assert.equal(await page.locator('[data-wp-router-region]').count(), 1, 'Only Recent videos is interactive');
		const next = page.locator(`${region} .wp-block-query-pagination-next`);
		assert.equal(new URL(await next.getAttribute('href'), base).pathname, '/page/2/');
		await next.click();
		await page.waitForURL('**/page/2/');
		await waitForIds(page, second);
		assert.equal(await page.evaluate(() => window.__paginationDocument), true, 'No document reload');
		assert.equal(await page.evaluate(() => window.__paginationHero === document.querySelector('.wptv-home__hero')), true, 'Hero is untouched');
		await page.waitForFunction(() => !!document.activeElement?.closest('.wptv-home__recent .wp-block-post-template'));
		await page.goBack();
		await waitForIds(page, first);
		assert.equal(new URL(page.url()).pathname, '/');
		await page.goForward();
		await waitForIds(page, second);
		assert.equal(new URL(page.url()).pathname, '/page/2/');
		assert.equal(await page.evaluate(() => window.__paginationDocument), true, 'History stays client-side');
		await page.waitForFunction(() => !!document.querySelector('.wptv-home__recent .wp-block-post.wptv-card--clickable'));
		await page.evaluate(() => {
			window.__cardClicks = 0;
			document.querySelector('.wptv-home__recent .wp-block-post-title a').addEventListener('click', (event) => { event.preventDefault(); window.__cardClicks++; });
			window.open = (url) => { window.__cardOpened = url; };
		});
		await page.locator(`${cards} .wp-block-post-date`).first().click();
		assert.equal(await page.evaluate(() => window.__cardClicks), 1, 'Replaced card expands to its current title');
		await page.locator(`${cards} .wp-block-post-date`).first().click({ modifiers: ['Control'] });
		assert.equal(await page.evaluate(() => window.__cardOpened), await page.locator(`${cards} .wp-block-post-title a`).first().getAttribute('href'), 'Modified card click is preserved');
		await enhanced.close();

		const fallback = await context();
		let failedFetches = 0;
		await fallback.route('**/page/2/**', (route) => {
			if (route.request().resourceType() === 'fetch') { failedFetches++; return route.abort(); }
			return route.continue();
		});
		const failure = await fallback.newPage();
		await open(failure);
		await ready(failure);
		await failure.locator(`${region} .wp-block-query-pagination-next`).click();
		await failure.waitForURL('**/page/2/');
		await waitForIds(failure, second);
		assert.ok(failedFetches > 0, 'AJAX failure was exercised');
		assert.equal(await failure.evaluate(() => window.__paginationDocument), undefined, 'Failed AJAX falls back to document navigation');
		await fallback.close();
		console.log('PASS: direct/no-JS page two, unchanged page size and links, AJAX region update, focus, Back/Forward, dynamic cards, modified clicks and network-failure fallback.');
	} finally {
		await browser.close();
	}
})().catch((error) => { console.error(error); process.exitCode = 1; });
