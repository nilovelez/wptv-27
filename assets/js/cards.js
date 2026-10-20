/**
 * Expand pointer activation without extra links, tab stops or player overlays.
 * Without JavaScript, the native title links remain fully functional.
 */
(() => {
	'use strict';

	const cards = document.querySelectorAll(
		'.wptv-home__videos > .wp-block-post, ' +
		'.wptv-listing__videos > .wp-block-post, ' +
		'.wptv-taxonomy__secondary > .wp-block-post, ' +
		'.wptv-taxonomy__caption'
	);
	const interactive = 'a, button, input, select, textarea, label, summary, video, audio, iframe, [contenteditable], [role="button"], [role="link"]';

	cards.forEach((card) => {
		const title = card.querySelector('.wp-block-post-title a');
		if (!title) {
			return;
		}
		card.classList.add('wptv-card--clickable');

		const activate = (event) => {
			if (event.defaultPrevented || event.altKey || !(event.target instanceof Element) ||
				event.target.closest(interactive) || window.getSelection()?.toString() ||
				(event.type === 'click' && event.button !== 0) ||
				(event.type === 'auxclick' && event.button !== 1)) {
				return;
			}
			if (event.ctrlKey || event.metaKey || event.shiftKey || event.button === 1) {
				event.preventDefault();
				window.open(title.href, '_blank', 'noopener');
				return;
			}
			title.click();
		};

		// Prevent middle-button autoscroll before auxclick opens the title URL.
		card.addEventListener('mousedown', (event) => {
			if (event.button === 1 && !event.defaultPrevented && event.target instanceof Element &&
				!event.target.closest(interactive) && !window.getSelection()?.toString()) {
				event.preventDefault();
			}
		});
		card.addEventListener('click', activate);
		card.addEventListener('auxclick', activate);
	});
})();
