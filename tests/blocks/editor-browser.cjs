/* Focused video-editor checks. Opens posts without saving; no content is changed. */
const assert = require('node:assert/strict');
const { chromium } = require(process.env.WPTV_PLAYWRIGHT_PATH || 'playwright');
const base = process.env.WPTV_TEST_URL || 'http://localhost:8882';
const blockPost = Number(process.env.WPTV_BLOCK_POST || 141567);
const shortcodePost = Number(process.env.WPTV_SHORTCODE_POST || 141575);

(async () => {
	const browser = await chromium.launch({ headless: true });
	const errors = [];
	try {
		const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
		const page = await context.newPage();
		page.on('pageerror', (error) => errors.push(error.message));
		const forbidden = [];
		page.on('response', (response) => { if (response.status() === 403) forbidden.push(response.url()); });
		await page.goto(`${base}/studio-auto-login?redirect_to=%2Fwp-admin%2F`, { waitUntil: 'domcontentloaded', timeout: 60000 });

		async function open(id) {
			await page.goto(`${base}/wp-admin/post.php?post=${id}&action=edit`, { waitUntil: 'domcontentloaded', timeout: 60000 });
			await page.waitForFunction(() => window.wp?.data?.select('core/editor')?.__unstableIsEditorReady?.(), null, { timeout: 60000 });
			await page.evaluate(() => window.wp.data.dispatch('core/preferences').set('core', 'welcomeGuide', false));
			await page.waitForTimeout(1500);
		}
		const state = () => page.evaluate(() => {
			const { select } = window.wp.data;
			const blocks = select('core/block-editor').getBlocks();
			return {
				blocks: blocks.map((block) => ({ name: block.name, guid: block.attributes.guid })),
				dirty: select('core/editor').isEditedPostDirty(),
				valid: select('core/block-editor').isValidTemplate(),
				alignSupport: window.wp.blocks.getBlockSupport('videopress/video', 'align', null),
				inserter: select('core/block-editor').canInsertBlockType('core/paragraph'),
				notices: select('core/notices').getNotices().map((notice) => notice.content),
			};
		});

		// Shortcode-only post: converted in the editor, never saved by this test.
		await open(shortcodePost);
		let result = await state();
		const originalContent = await page.evaluate(() => window.wp.data.select('core/editor').getCurrentPostAttribute('content'));
		const originalGuid = originalContent.match(/^\s*\[wpvideo\s+([A-Za-z0-9]{8})(?:\s[^\]]*)?\]\s*$/)?.[1];
		assert.ok(originalGuid, 'Shortcode fixture must still contain an unsaved legacy shortcode');
		assert.deepEqual(result.blocks, [{ name: 'videopress/video', guid: originalGuid }], 'Shortcode post becomes one VideoPress block');
		assert.equal(result.valid, true, 'No template mismatch notice');
		assert.equal(result.dirty, true, 'Conversion waits for a save');
		assert.ok(result.notices.some((text) => /converted/.test(text)), 'Conversion is announced');
		await page.evaluate(() => window.wp.data.dispatch('core/editor').resetEditorBlocks(window.wp.blocks.parse(window.wp.data.select('core/editor').getCurrentPostAttribute('content'))));

		// Block post: restrictions and data panel.
		await open(blockPost);
		result = await state();
		assert.deepEqual(result.blocks.map((block) => block.name), ['videopress/video'], 'Only the video block remains');
		assert.equal(result.alignSupport, false, 'Alignment is removed from the video block');
		assert.equal(result.inserter, false, 'No other blocks can be inserted');
		for (const selector of ['.editor-document-tools__inserter-toggle', '.editor-document-tools__document-overview-toggle']) {
			assert.equal(await page.locator(selector).count(), 1, `${selector} still exists in core`);
			assert.equal(await page.locator(selector).isVisible(), false, `${selector} is hidden for video posts`);
		}
		await page.evaluate(() => window.wp.data.dispatch('core/edit-post').openGeneralSidebar('edit-post/document'));
		for (const label of [/^Change author:/, /^Template options$/]) {
			const control = page.getByRole('button', { name: label, includeHidden: true });
			assert.equal(await control.count(), 1, `${label} remains registered`);
			assert.equal(await control.isVisible(), false, `${label} is hidden on video posts`);
		}
		const panel = page.locator('.components-panel__body', { hasText: 'Video details' });
		await panel.waitFor({ timeout: 15000 });
		for (const label of ['Language', 'Categories', 'Tags', 'Event', 'Producer WordPress.org username', 'Speakers', 'Description (excerpt)', 'Slides URL']) {
			assert.equal(await panel.getByText(label, { exact: true }).count(), 1, `${label} field is present`);
		}
		assert.deepEqual(await panel.locator('label').allTextContents(), ['Language', 'Categories', 'Tags', 'Event', 'Producer WordPress.org username', 'Speakers', 'Description (excerpt)', 'Slides URL'], 'Video details follows the submission form field order');
		await page.waitForFunction(() => document.querySelectorAll('.components-panel__body .components-form-token-field__token').length > 0, null, { timeout: 15000 });
		assert.ok((await panel.locator('textarea').inputValue()).startsWith('During COVID-19'), 'Excerpt is loaded');
		assert.equal(await panel.getByText('Separate with commas or the Enter key.', { exact: true }).count(), 0, 'Taxonomy fields omit repetitive help');
		const extendedStatus = page.locator('.edit-flow-extended-post-status');
		assert.equal(await extendedStatus.count(), 1, 'Edit Flow status integration remains registered');
		assert.equal(await extendedStatus.isVisible(), false, 'Published videos hide the disabled Edit Flow status box');
		assert.equal(await page.locator('.editor-post-status__toggle').isVisible(), true, 'Native Status control remains available');
		// Exercise the reactive status class without saving or publishing anything.
		await page.evaluate(() => window.wp.data.dispatch('core/editor').editPost({ status: 'pending' }));
		await page.locator('.edit-flow-extended-post-status-pending').waitFor();
		assert.equal(await extendedStatus.isVisible(), true, 'Nonpublished videos keep the custom-status selector');
		assert.equal(await extendedStatus.locator('select option[value="in-progress"]').count(), 1, 'Edit Flow custom statuses remain selectable');
		await page.evaluate(() => window.wp.data.dispatch('core/editor').editPost({ status: 'publish' }));
		await page.locator('.edit-flow-extended-post-status-publish').waitFor({ state: 'hidden' });
		for (const title of ['Speakers', 'Event', 'Language', 'Categories', 'Tags', 'Excerpt']) {
			assert.equal(await page.locator('.components-panel__body-toggle', { hasText: new RegExp(`^${title}$`) }).count(), 0, `Native ${title} panel is removed`);
		}
		await page.evaluate(() => {
			const { select, dispatch } = window.wp.data;
			dispatch('core/block-editor').selectBlock(select('core/block-editor').getBlocks()[0].clientId);
			dispatch('core/edit-post').openGeneralSidebar('edit-post/block');
		});
		await page.waitForTimeout(500);
		assert.equal(await page.locator('[role="tab"][data-tab-id="edit-post/block"]').isVisible(), false, 'Block tab is hidden');
		assert.equal(await page.evaluate(() => window.wp.data.select('core/edit-post').getActiveGeneralSidebarName()), 'edit-post/document', 'Selecting the video keeps the Post sidebar');
		await page.evaluate(() => window.wp.data.dispatch('core/edit-post').closeGeneralSidebar());
		await page.waitForTimeout(100);
		assert.ok(!await page.evaluate(() => window.wp.data.select('core/edit-post').getActiveGeneralSidebarName()), 'Closing the sidebar does not reopen it');
		await page.evaluate(() => window.wp.data.dispatch('core/edit-post').openGeneralSidebar('edit-post/document'));
		assert.equal(await page.locator('.block-editor-block-inspector').evaluateAll((nodes) => nodes.some((node) => getComputedStyle(node).display !== 'none')), false, 'Video block settings sidebar is hidden');
		await page.locator('.block-editor-block-toolbar button', { hasText: 'Replace' }).waitFor({ timeout: 15000 });
		const buttons = await page.evaluate(() => [...document.querySelectorAll('.block-editor-block-toolbar button')]
			.filter((node) => getComputedStyle(node).display !== 'none')
			.map((node) => node.getAttribute('aria-label') || node.innerText.trim()));
		for (const label of ['Replace', 'Manage subtitles']) {
			assert.ok(buttons.includes(label), `${label} stays in the toolbar`);
		}
		for (const label of ['Add caption', 'Poster image', 'Manage chapters']) {
			assert.ok(!buttons.includes(label), `${label} is hidden`);
		}
		const handle = page.frameLocator('iframe[name=editor-canvas]').locator('[data-type="videopress/video"] .components-resizable-box__handle');
		assert.equal(await handle.evaluateAll((nodes) => nodes.some((node) => getComputedStyle(node).display !== 'none')), false, 'Resize handle is hidden');
		assert.equal((await state()).dirty, false, 'Opening the block post leaves it unchanged');
		const canvasTitle = () => page.frameLocator('iframe[name=editor-canvas]').locator('.editor-post-title');
		await canvasTitle().waitFor();
		assert.equal(await canvasTitle().evaluate((node) => getComputedStyle(node).fontSize), '30px', 'Video editing title remains compact');

		// Pages must retain their title typography and standard publication controls.
		await open(Number(process.env.WPTV_PAGE_POST || 133512));
		await canvasTitle().waitFor();
		assert.ok(parseFloat(await canvasTitle().evaluate((node) => getComputedStyle(node).fontSize)) > 30, 'Page title does not inherit compact video styling');
		assert.equal(await page.getByRole('button', { name: /^Change author:/ }).isVisible(), true, 'Pages retain Author');
		assert.equal(await page.getByRole('button', { name: 'Template options', exact: true }).isVisible(), true, 'Pages retain Template');
		assert.equal(await page.locator('.editor-document-tools__inserter-toggle').isVisible(), true, 'Pages retain the block inserter');
		assert.equal(await page.locator('#wptv-editor-js').count(), 0, 'Video-only editor script is not loaded on pages');

		// Optional: any post with _wptv_submitted_video. Approves only fields that create no terms.
		const submissionPost = Number(process.env.WPTV_SUBMISSION_POST || 0);
		if (submissionPost) {
			await open(submissionPost);
			const review = page.locator('#wptv-submission-root .wptv-review');
			await review.waitFor({ timeout: 15000 });
			const approve = (label) => review.locator('.wptv-review__field', { hasText: label }).getByRole('button', { name: 'Approve' }).click();
			assert.equal(await page.locator('.edit-post-meta-boxes-area #wptv-submission').count(), 1, 'Review box sits with the meta boxes below the content');
			const before = await page.evaluate(() => (window.wp.data.select('core/editor').getEditedPostAttribute('language') || []).length);
			await approve('Title');
			await approve('Language');
			await approve('Description');
			const slides = await page.evaluate(() => !!window.wptvSubmission.slides);
			if (slides) await approve('Slides');
			const firstCategory = await page.evaluate(() => window.wptvSubmission.categories[0]?.name);
			if (firstCategory) await review.getByRole('button', { name: firstCategory, exact: true }).click();
			const expected = 3 + Number(slides) + Number(!!firstCategory);
			await page.waitForFunction((count) => [...document.querySelectorAll('#wptv-submission-root button')].filter((node) => node.textContent === 'Approved').length === count, expected, { timeout: 15000 });
			const edited = await page.evaluate(() => {
				const get = (key) => window.wp.data.select('core/editor').getEditedPostAttribute(key);
				return { title: get('title'), excerpt: get('excerpt'), slides: get('meta')._wptv_slides_url, categories: get('categories'), speakers: get('speakers'), submission: window.wptvSubmission };
			});
			assert.equal(edited.title, edited.submission.title, 'Approve copies the title');
			assert.equal(edited.excerpt, edited.submission.description, 'Approve copies the description to the excerpt');
			if (slides) assert.equal(edited.slides, edited.submission.slides, 'Approve copies the slides URL');
			if (firstCategory) assert.ok(!edited.categories.includes(edited.submission.defaultCategory) && edited.categories.includes(edited.submission.categories[0].id), 'First category approval adds it and removes the WordCampTV default');
			assert.ok((await page.evaluate(() => (window.wp.data.select('core/editor').getEditedPostAttribute('language') || []).length)) >= Math.max(before, 1), 'Approve assigns the language');
			await review.scrollIntoViewIfNeeded();
			console.log('PASS: submission review box below the content copies title, language, description, slides and categories without saving.');
		}
		assert.deepEqual(errors, [], 'No editor JavaScript errors');
		if (forbidden.length) console.log('403 responses (review):\n' + [...new Set(forbidden)].join('\n'));
		await page.screenshot({ path: process.env.WPTV_EDITOR_SCREENSHOT || 'editor-video.png' });
		console.log('PASS: shortcode conversion without saving, single locked video block, no alignment/inserter, Video details fields populated, native panels removed, hidden block settings, toolbar limited to replace/subtitles, no resize handle and no JavaScript errors.');
	} finally {
		await browser.close();
	}
})().catch((error) => { console.error(error); process.exitCode = 1; });
