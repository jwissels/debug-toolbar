/**
 * help tabbed navigation inside the display's iframe
 */
setTimeout(function() {
	window.focus();
}, 100);

/**
 * allow the parent to close the detail
 * @see https://stackoverflow.com/questions/251420/invoking-javascript-code-in-an-iframe-from-the-parent-page/251645#251645
 */
if (window.parent.iframeCloseDebugCallback != undefined) {
	// overwrite the placeholder
	window.parent.iframeCloseDebugCallback = function() {
		// keep the animation calm by first closing the display completely
		setTimeout(function() {
			$('#detail-container, .detail-container').removeClass('detail-active');
		}, 100);
	};
}

/**
 * shortcuts
 * - Shift+D: open/close display
 * - Escape:  close display
 */
if (parent.window.closeDebugDisplay != undefined) {
	window.addEventListener('keydown', function(event) {
		const isShiftD = (event.ctrlKey == false && event.altKey == false && event.shiftKey && event.keyCode == 68);
		const isEscape = (event.ctrlKey == false && event.altKey == false && event.shiftKey == false && event.keyCode == 27);
		
		if (isShiftD) {
			event.preventDefault();
			parent.window.closeDebugDisplay();
		}
		if (isEscape) {
			event.preventDefault();
			parent.window.closeDebugDisplay();
		}
	});
}
