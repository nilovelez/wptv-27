/** Focused, read-only card checks. Reuses an existing Playwright installation. */
'use strict';
const assert = require('node:assert/strict');
const { chromium } = require(process.env.WPTV_PLAYWRIGHT_PATH || 'playwright');
const base = process.env.WPTV_TEST_URL || 'http://localhost:8882';
let checks = 0;
function check(value, label) { assert.ok(value, label); checks++; }
const selector = '.wptv-home__videos > .wp-block-post, .wptv-listing__videos > .wp-block-post, .wptv-taxonomy__secondary > .wp-block-post, .wptv-taxonomy__caption';
(async () => {
	const browser = await chromium.launch({ headless: true });
	try {
		const page = await browser.newPage();
		for (const path of ['/', '/category/how-to/', '/event/wordcamp-europe-2023/', '/category/how-to/page/2/']) {
			for (const width of [375, 1440]) {
				await page.setViewportSize({ width, height: 1000 });
				const response = await page.goto(base + path, { waitUntil: 'domcontentloaded' });
				await page.waitForSelector('.wptv-card--clickable');
				await page.evaluate(() => document.fonts.ready);
				check(response.status() === 200, `${path} status at ${width}`);
				check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${path} no overflow at ${width}`);
				const cards = page.locator(selector);
				check(await cards.count() > 0, `${path} renders cards`);
				check(await cards.evaluateAll(items => items.every(card => card.querySelectorAll('a').length === 1 && card.querySelector('.wp-block-post-title a') && !card.hasAttribute('tabindex') && card.classList.contains('wptv-card--clickable'))), `${path} one real title link and no duplicate tab stop`);
				check(await page.locator('.wptv-card__speakers').evaluateAll(items => items.length > 0 && items.every(el => el.textContent.trim().endsWith(':') && !el.querySelector('a'))), `${path} plain speakers with trailing colon`);
				check(await page.locator('.wptv-card__thumbnail').evaluateAll(items => items.every(el => !el.querySelector('a') && el.querySelector('img').alt === '')), `${path} unlinked decorative thumbnails`);
				const dated = page.locator('.wptv-home__recent .wp-block-post, .wptv-listing__videos > .wp-block-post');
				check(await dated.evaluateAll(items => items.length > 0 && items.every(card => {
					const parts = [...card.children];
					const index = name => parts.findIndex(el => el.matches(name));
					const image = index('.wp-block-post-featured-image');
					const speaker = index('.wptv-card__speakers');
					const title = index('.wp-block-post-title');
					const date = index('.wp-block-post-date');
					return image === 0 && title > image && date > title && (speaker < 0 || (speaker > image && speaker < title)) && !parts[date].querySelector('a');
				})), `${path} thumbnail, speakers, title, date ordering`);
				if (path === '/') {
					check(await page.locator('.wptv-home__featured .wp-block-post-date').count() === 0, 'Featured retains its undated presentation');
				} else if (!path.includes('/page/2/')) {
					check(await page.locator('.wptv-taxonomy__secondary > li').count() === 5, 'Five secondary videos preserved');
					check(await page.locator('.wptv-taxonomy__spotlight .taxonomy-event').count() === 0, 'Spotlight never shows event terms');
					check(await page.locator('.wptv-taxonomy__lead iframe').count() === 1 && await page.locator('.wptv-taxonomy__lead > li.wptv-card--clickable').count() === 0, 'Player is outside clickable caption');
				} else {
					check(await page.locator('.wptv-taxonomy__spotlight').count() === 0, 'Later pages remain unpartitioned');
				}
				console.log(`Verified card markup ${path} at ${width}px.`);
			}
		}

		await page.goto(base + '/', { waitUntil: 'domcontentloaded' });
		await page.waitForSelector('.wptv-card--clickable');
		await page.evaluate(() => {
			window.cardClicks = [];
			window.cardPopups = [];
			window.open = (url, target, features) => { window.cardPopups.push({ url, target, features }); return null; };
			document.addEventListener('click', event => {
				const link = event.target.closest('.wp-block-post-title a');
				if (link) { event.preventDefault(); window.cardClicks.push(link.href); }
			});
		});
		for (const section of ['.wptv-home__featured', '.wptv-home__recent']) {
			const card = page.locator(`${section} .wp-block-post`).first();
			const title = card.locator('.wp-block-post-title a');
			const url = await title.getAttribute('href');
			await card.locator('figure').click();
			check(await page.evaluate(() => window.cardClicks.at(-1)) === url, `${section} thumbnail activates title URL`);
			await card.locator('.wptv-card__speakers').click();
			check(await page.evaluate(() => window.cardClicks.at(-1)) === url, `${section} speaker activates title URL`);
			const before = await page.evaluate(() => window.cardClicks.length);
			await title.focus();
			await page.keyboard.press('Enter');
			check(await page.evaluate(() => window.cardClicks.length) === before + 1, 'Native keyboard activation is not duplicated');
			const popups = await page.evaluate(() => window.cardPopups.length);
			await card.locator('figure').click({ modifiers: ['Control'] });
			await card.locator('figure').click({ button: 'middle' });
			check(await page.evaluate(({ url, popups }) => window.cardPopups.length === popups + 2 && window.cardPopups.slice(-2).every(p => p.url === url && p.target === '_blank' && p.features === 'noopener'), { url, popups }), 'Modified and middle clicks open the title safely without autoscroll');
			const count = await page.evaluate(() => window.cardClicks.length);
			await card.evaluate(el => {
				const range = document.createRange();
				range.selectNodeContents(el.querySelector('.wptv-card__speakers'));
				getSelection().removeAllRanges(); getSelection().addRange(range);
				el.dispatchEvent(new MouseEvent('click', { bubbles: true }));
				getSelection().removeAllRanges();
				const button = document.createElement('button');
				el.append(button); button.click(); button.remove();
				el.addEventListener('click', event => event.preventDefault(), { capture: true, once: true });
				el.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
			});
			check(await page.evaluate(() => window.cardClicks.length) === count, 'Selection, interactive controls and cancelled clicks are preserved');
		}

		await page.goto(base + '/category/how-to/', { waitUntil: 'domcontentloaded' });
		await page.waitForSelector('.wptv-card--clickable');
		await page.evaluate(() => {
			window.cardDestination = null;
			document.addEventListener('click', event => {
				const link = event.target.closest('.wp-block-post-title a');
				if (link) { event.preventDefault(); window.cardDestination = link.href; }
			});
		});
		for (const scope of ['.wptv-taxonomy__caption', '.wptv-taxonomy__secondary > li', '.wptv-listing__videos > li']) {
			const card = page.locator(scope).first();
			const destination = await card.locator('.wp-block-post-title a').getAttribute('href');
			await card.click({ position: { x: 5, y: 5 } });
			check(await page.evaluate(() => window.cardDestination) === destination, `${scope} padding uses its own title URL`);
		}

		const nojs = await browser.newContext({ javaScriptEnabled: false });
		const plain = await nojs.newPage();
		await plain.goto(base + '/', { waitUntil: 'domcontentloaded' });
		check(await plain.locator('.wptv-card--clickable').count() === 0, 'No pointer enhancement without JavaScript');
		const native = plain.locator('.wptv-home__recent .wp-block-post-title a').first();
		const destination = await native.getAttribute('href');
		check(await native.evaluate(el => getComputedStyle(el, '::after').content === 'none'), 'No stretched-link overlay on video cards');
		await Promise.all([plain.waitForURL(destination, { waitUntil: 'domcontentloaded' }), native.click()]);
		check(plain.url() === destination, 'Native title navigation works without JavaScript');
		await nojs.close();
		console.log(`PASS: ${checks} focused card checks. No database writes.`);
	} finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
