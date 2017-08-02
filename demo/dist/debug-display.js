/**
 * help tabbed navigation inside the display's iframe
 */
setTimeout(function() {
	window.focus();
}, 100);

/**
 * shortcuts
 * - Shift+D: open/close display
 * - Escape:  close display
 */
window.addEventListener('keydown', function(event) {
	if (event.ctrlKey == false && event.altKey == false && event.shiftKey && event.keyCode == 68) {
		event.preventDefault();
		top.window.closeDebugDisplay();
	}
	if (event.ctrlKey == false && event.altKey == false && event.shiftKey == false && event.keyCode == 27) {
		event.preventDefault();
		top.window.closeDebugDisplay();
	}
});
