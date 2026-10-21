/* Form checks with intercepted upload responses: never creates posts or sends video to VideoPress. */
const assert = require('node:assert/strict');
const { chromium } = require(process.env.WPTV_PLAYWRIGHT_PATH || 'playwright');
const base = process.env.WPTV_TEST_URL || 'http://localhost:8882';
(async () => {
	const browser = await chromium.launch({ headless: true });
	try {
		const context = await browser.newContext({ viewport: { width: 1440, height: 1248 } });
		const page = await context.newPage();
		const errors = [];
		page.on('pageerror', (error) => errors.push(error.message));
		const open = async (query = '') => {
			await page.goto(base + '/submit-video/' + query, { waitUntil: 'networkidle' });
			await page.locator('#wptv_video_upload').waitFor({ state: 'visible' });
		};
		await open();
		assert.equal(await page.locator('h1').count(), 1, 'One page title');
		assert.equal(await page.locator('#video-upload-form').count(), 1, 'One fixed form');
		assert.ok(await page.locator('.wptv-submission-layout > .wp-block-column:last-child .wp-block-post-content').textContent(), 'Editable instructions render without assuming their wording');
		assert.deepEqual(await page.locator('#video-upload-form').evaluate((form) => [...form.querySelectorAll(':scope > .wptv-submit__row')].map((row) => row.classList.contains('location') ? 'location' : row.classList.contains('cats') ? 'cats' : row.querySelector('input,textarea').id)), ['wptv_uploaded_by', 'wptv_email', 'wptv_video_title', 'wptv_speakers', 'wptv_language', 'wptv_date', 'location', 'cats', 'wptv_event', 'wptv_producer_username', 'wptv_video_description', 'wptv_slides_url', 'wptv_file'], 'Requested field order');
		assert.equal(await page.locator('.cats #wptv_video_wordcamp').count(), 1, 'WordCamp checkbox shares Category heading');
		const desktopColumns = await page.locator('.wptv-submission-layout > .wp-block-column').evaluateAll((nodes) => nodes.map((n) => n.getBoundingClientRect().width));
		assert.ok(Math.abs(desktopColumns[0] / desktopColumns[1] - 2) < 0.1, 'Desktop columns use a 2:1 ratio');
		assert.equal(await page.locator('.wptv-submit__guidelines-link').isVisible(), false, 'Guidelines prompt hidden beside instructions');
		assert.equal(await page.locator('.wptv-submit__guidelines-link a').getAttribute('href'), '#guidelines');
		assert.equal(await page.locator('#guidelines.wp-block-column').count(), 1, 'Guidelines anchor belongs to the instruction column');
		assert.equal(await page.locator('#wptv_honey_container').isVisible(), false, 'Honeypot hidden');
		let uploaded = false;
		await page.route('**/wp-admin/admin-post.php*', async (route) => {
			uploaded = true;
			await route.abort('failed');
		});
		await page.locator('#wptv_video_upload').click();
		assert.equal(await page.locator('[aria-invalid="true"]').count(), 3, 'Name, email and file required');
		assert.equal(uploaded, false, 'Invalid submission never leaves browser');
		await page.locator('#wptv_uploaded_by').fill('Submission test');
		await page.locator('#wptv_email').fill('test@example.com');
		await page.locator('#wptv_speakers').fill('Jane and John');
		await page.locator('#wptv_speakers').blur();
		assert.equal(await page.locator('#wptv_speakers').inputValue(), 'Jane, John');
		await page.locator('#wptv_video_wordcamp').check();
		await page.locator('#wptv_date').fill('2025-05-04');
		assert.match(await page.locator('#wptv_event').inputValue(), /^WordCamp .*2025$/);
		await page.locator('#wptv_event').fill('My custom event');
		await page.locator('#wptv_date').fill('2026-05-04');
		assert.equal(await page.locator('#wptv_event').inputValue(), 'My custom event', 'Manual event preserved');
		await page.locator('#wptv_file').setInputFiles({ name: 'sample.mp4', mimeType: 'video/mp4', buffer: Buffer.from('browser-test-only') });
		await page.locator('#wptv_video_upload').click();
		await page.locator('#upload-progress .status').filter({ hasText: 'Upload failed.' }).waitFor();
		assert.equal(uploaded, true, 'Valid form starts XHR');
		assert.equal(await page.locator('#wptv_video_upload').isEnabled(), true, 'Network failure enables retry');
		await page.unroute('**/wp-admin/admin-post.php*');
		await page.route('**/wp-admin/admin-post.php*', (route) => route.fulfill({ status: 200, contentType: 'text/plain', body: '<html>PHP error</html>' }));
		await page.locator('#wptv_video_upload').click();
		await page.locator('#upload-progress .status').filter({ hasText: 'unexpected response' }).waitFor();
		assert.equal(await page.locator('#wptv_video_upload').isEnabled(), true, 'Malformed response enables retry');
		await page.unroute('**/wp-admin/admin-post.php*');
		await page.route('**/wp-admin/admin-post.php*', (route) => {
			assert.match(route.request().postData(), /name="action"\r\n\r\nwptv_video_upload/);
			return route.fulfill({ status: 200, contentType: 'text/plain', body: base + '/submit-video/?success=1&wptv_event=My+custom+event' });
		});
		await page.locator('#wptv_video_upload').click();
		await page.waitForURL('**/submit-video/?success=1*');
		await page.locator('.wptv-submit__notice.is-success').waitFor();
		assert.equal(await page.locator('#wptv_event').inputValue(), 'My custom event', 'Redirect prefill retained');
		await open('?error=14&wptv_producer_username=invalid-test-user');
		assert.match(await page.locator('.wptv-submit__notice.is-error').textContent(), /valid WordPress.org username/);
		assert.equal(await page.locator('#wptv_producer_username').inputValue(), 'invalid-test-user');
		// Return to a clean form: error/success redirects intentionally replace the prompt.
		await open();
		await page.setViewportSize({ width: 900, height: 1000 });
		assert.equal(await page.locator('.wptv-submit__guidelines-link').isVisible(), true, 'Guidelines prompt shown when page columns stack');
		const tablet = await page.locator('.wptv-submission-layout > .wp-block-column').evaluateAll((nodes) => nodes.map((n) => { const r = n.getBoundingClientRect(); return { top: r.top, bottom: r.bottom }; }));
		assert.ok(tablet[1].top >= tablet[0].bottom, 'Page columns stack on tablets');
		assert.equal(await page.locator('.wptv-submit__row').first().evaluate((row) => row.querySelector('label').getBoundingClientRect().right <= row.querySelector('input').getBoundingClientRect().left), true, 'Labels and inputs remain side by side on tablets');
		await page.setViewportSize({ width: 390, height: 844 });
		const positions = await page.locator('.wptv-submission-layout > .wp-block-column').evaluateAll((nodes) => nodes.map((node) => { const r = node.getBoundingClientRect(); return { top: r.top, bottom: r.bottom }; }));
		assert.ok(positions[1].top >= positions[0].bottom, 'Form before instructions on mobile');
		assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), true, 'No mobile overflow');
		assert.deepEqual(errors, [], 'No JS errors');
		console.log('PASS: fixed form, editable instructions column, validation, event/speaker behavior, XHR retry/response handling, redirect notices/prefill and mobile order. Upload responses simulated; no records created.');
	} finally {
		await browser.close();
	}
})().catch((error) => { console.error(error); process.exitCode = 1; });
