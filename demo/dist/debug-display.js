/**
 * help tabbed navigation inside the display's iframe
 */
setTimeout(function() {
	window.focus();
}, 100);

/**
 * allow the parent to close the detail
 * @see https://stackoverflow.com/a/251645/230422
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
		if (event.ctrlKey == false && event.altKey == false && event.shiftKey && event.keyCode == 68) {
			event.preventDefault();
			parent.window.closeDebugDisplay();
		}
		if (event.ctrlKey == false && event.altKey == false && event.shiftKey == false && event.keyCode == 27) {
			event.preventDefault();
			parent.window.closeDebugDisplay();
		}
	});
}
