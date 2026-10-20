/**
 * Read-only browser checks for the approved visual implementation.
 * Run with an existing Playwright installation; no theme build/dependency required.
 * WPTV_PLAYWRIGHT_PATH optionally points to that installation's module directory.
 */
'use strict';
const assert = require('node:assert/strict');
const { chromium } = require(process.env.WPTV_PLAYWRIGHT_PATH || 'playwright');
const base = process.env.WPTV_TEST_URL || 'http://localhost:8882';
let checks = 0;
function check(condition, message) {
	assert.ok(condition, message);
	checks++;
}
(async () => {
	const browser = await chromium.launch({ headless: true });
	try {
		const page = await browser.newPage();
		for (const width of [320, 375, 600, 782, 890, 960, 1024, 1040, 1080, 1440]) {
			await page.setViewportSize({ width, height: 1000 });
			const response = await page.goto(base + '/', { waitUntil: 'networkidle' });
			await page.evaluate(() => document.fonts.ready);
			check(response.status() === 200, `Home status at ${width}`);
			const layout = await page.evaluate(() => {
				const heading = document.querySelector('h1');
				const text = heading.firstChild;
				const word = document.createRange();
				word.setStart(text, text.textContent.indexOf('WordPress.tv'));
				word.setEnd(text, text.length);
				return {
					width: document.documentElement.scrollWidth,
					wordLines: word.getClientRects().length,
					fonts: document.fonts.check('400 16px Inter') && document.fonts.check('400 50px "EB Garamond"'),
					columns: [...document.querySelectorAll('.wptv-home__hero > .wp-block-column')].map(el => el.getBoundingClientRect().width),
					buttons: [...document.querySelectorAll('.wptv-home__welcome .wp-block-button__link')].map(el => getComputedStyle(el).padding),
					titleSize: parseFloat(getComputedStyle(heading).fontSize),
				};
			});
			check(layout.width <= width, `No home overflow at ${width}`);
			check(layout.wordLines === 1, `Unbroken WordPress.tv at ${width}`);
			check(layout.fonts, `Local font faces available at ${width}`);
			check(layout.buttons.every(padding => padding === '14px 32px'), `Equal hero button padding at ${width}`);
			if (width >= 782 && width < 1080) {
				check(Math.abs(layout.columns[0] - layout.columns[1]) < 1, `Tablet 1:1 at ${width}`);
				check(layout.titleSize >= 50 && layout.titleSize <= 59, `Reduced tablet heading at ${width}`);
			} else if (width >= 1080) {
				check(Math.abs(layout.columns[0] / layout.columns[1] - 5 / 7) < 0.01, `Desktop 5:7 at ${width}`);
			}
			console.log(`Home ${width}px: ${layout.titleSize}px title, no overflow.`);
		}
		check(await page.locator('.wptv-home__categories-grid .wp-block-term-name a').evaluateAll(links => links.length > 0 && links.every(link => !['/category/year/', '/category/location/'].includes(new URL(link.href).pathname))), 'Structural categories excluded only from home cards');
		check(await page.locator('.wptv-footer .wp-block-site-title').evaluate(el => getComputedStyle(el).fontFamily === getComputedStyle(document.querySelector('.wptv-header .wp-block-site-title')).fontFamily), 'Footer brand uses the header font family');
		const outline = page.locator('.is-style-outline .wp-block-button__link');
		await outline.hover();
		check(await outline.evaluate(el => getComputedStyle(el).backgroundColor === 'rgb(33, 63, 212)' && getComputedStyle(el).color === 'rgb(255, 255, 255)'), 'Outline button hover');
		check(await page.locator('.wptv-card__thumbnail img').evaluateAll(images => images.length > 0 && images.every(el => el.alt === '')), 'Decorative card thumbnails');
		check(await page.locator('.wptv-card__thumbnail a').count() === 0, 'Card thumbnails have no links or duplicate tab stops');
		const eventsLink = page.getByRole('link', { name: 'All WordCamp Videos', exact: true });
		check(new URL(await eventsLink.getAttribute('href'), base).pathname === '/category/wordcamptv/', 'Confirmed temporary events link');
		await page.setViewportSize({ width: 375, height: 844 });
		const toggle = page.locator('.wp-block-navigation__responsive-container-open');
		check(await toggle.getAttribute('aria-expanded') === 'false', 'Closed mobile state');
		await toggle.click();
		check(await toggle.getAttribute('aria-expanded') === 'true', 'Open mobile state');
		check(!!(await toggle.getAttribute('aria-controls')), 'Mobile dialog association');
		check(await page.locator('.is-menu-open input[type="search"]').isVisible(), 'Search in mobile menu');
		check(await page.locator('.is-menu-open .wptv-header__submit a').isVisible(), 'Submit action in mobile menu');
		await page.waitForFunction(() => document.querySelector('.is-menu-open')?.contains(document.activeElement));
		await page.keyboard.press('Tab');
		check(await page.locator('.is-menu-open').evaluate(el => el.contains(document.activeElement)), 'Focus remains in mobile dialog');
		await page.keyboard.press('Escape');
		await page.waitForFunction(() => document.querySelector('.wp-block-navigation__responsive-container-open') === document.activeElement);
		check(await toggle.getAttribute('aria-expanded') === 'false', 'Escape closes mobile menu');
		check(await toggle.evaluate(el => el === document.activeElement), 'Core returns focus to toggle');
		const search = page.locator('.wptv-header input[type="search"]');
		await toggle.click();
		await search.focus();
		check(await page.locator('.wptv-header .wp-block-search__inside-wrapper').evaluate(el => getComputedStyle(el).outlineStyle === 'solid'), 'Visible search focus');
		await page.keyboard.press('Escape');
		const paths = [
			['/2024/04/10/wp-cli-for-php-developers-3/', 200],
			['/2024/04/10/intentional-designing-crafting-purposeful-digital-experiences/', 200],
			['/2026/05/09/nilo-velez-test/', 200],
			['/page/2/', 200],
			['/category/how-to/', 200],
			['/category/how-to/page/2/', 200],
			['/event/wordcamp-europe-2023/', 200],
			['/event/wordcamp-europe-2023/page/2/', 200],
			['/event/wordcamp-zaragoza-2023/page/2/', 200],
			['/tag/featured/', 200],
			['/?s=wordpress', 200],
			['/?s=wptv-no-matching-video-9fa3b7', 200],
			['/speakers/milana-cap/', 200],
			['/about/', 200],
			['/wptv-design-missing-page/', 404],
		];
		for (const [path, status] of paths) {
			for (const width of [375, 1440]) {
				await page.setViewportSize({ width, height: 1000 });
				const response = await page.goto(base + path, { waitUntil: 'networkidle' });
				check(response.status() === status, `Status ${path} at ${width}`);
				check(await page.locator('h1').count() === 1, `One h1 ${path} at ${width}`);
				check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `No overflow ${path} at ${width}`);
				if (path.includes('/page/2/')) {
					check(await page.locator('.wptv-taxonomy__spotlight').count() === 0, `No later-page spotlight ${path}`);
				}
				if (path.includes('/2024/') || path.includes('/2026/')) {
					check(await page.locator('.wptv-single__intro .wp-block-post-title').evaluate(el => getComputedStyle(el).maxWidth === 'none' && Math.abs(el.getBoundingClientRect().width - el.parentElement.getBoundingClientRect().width) < 1), `Single title uses the available intro width at ${width}`);
					const player = page.locator('.wptv-single__player iframe[src*="/embed/"]');
					check(await player.count() === 1, `One stored VideoPress player at ${width}`);
					const bounds = await player.boundingBox();
					check(bounds.width > 300 && bounds.height > 150, `Nonzero player bounds at ${width}`);
					if (width < 600) check(Math.abs(bounds.x) < 1 && Math.abs(bounds.width - width) < 1, 'Edge-to-edge mobile player');
				}
			}
			console.log(`Verified ${path} at 375px and 1440px.`);
		}
		console.log(`PASS: ${checks} browser checks. No database writes.`);
	} finally {
		await browser.close();
	}
})().catch(error => { console.error(error); process.exitCode = 1; });
