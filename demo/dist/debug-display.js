/**
 * help tabbed navigation inside the display's iframe
 */
setTimeout(function() {
	window.focus();
}, 100);

/**
 * close the display
 */
var closeDebugDisplay = function() {
	top.window.closeDebugDisplay();
	
	// keep the animation calm by first closing the display completely
	setTimeout(function() {
		$('#detail-container, .detail-container').removeClass('detail-active');
	}, 100);
};

/**
 * shortcuts
 * - Shift+D: open/close display
 * - Escape:  close display
 */
window.addEventListener('keydown', function(event) {
	if (event.ctrlKey == false && event.altKey == false && event.shiftKey && event.keyCode == 68) {
		event.preventDefault();
		closeDebugDisplay();
	}
	if (event.ctrlKey == false && event.altKey == false && event.shiftKey == false && event.keyCode == 27) {
		event.preventDefault();
		closeDebugDisplay();
	}
});
