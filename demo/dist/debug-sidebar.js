window.onload = function() {
	/**
	 * help tabbed navigation inside the iframe
	 */
	setTimeout(function() {
		window.focus();
	}, 100);
	
	/**
	 * make accordion headings clickable, not only the link inside them
	 */
	var accordionTitles = document.getElementsByClassName('panel-heading');
	for (var i=0; i<accordionTitles.length; i++) {
		var accordionTitle = accordionTitles[i];
		accordionTitle.className = accordionTitle.className + ' clickable';
		accordionTitle.addEventListener('click', function(event) {
			var accordionLink = this.querySelectorAll('[data-toggle="collapse"]')[0];
			accordionLink.click();
		});
	}
	
	/**
	 * shortcuts
	 * - Shift+D: open/close the sidebar
	 * - Escape:  close the sidebar
	 */
	window.addEventListener('keydown', function(event) {
		if (event.ctrlKey == false && event.altKey == false && event.shiftKey && event.keyCode == 68) {
			event.preventDefault();
			top.window.closeDebugSidebar();
		}
		if (event.ctrlKey == false && event.altKey == false && event.shiftKey == false && event.keyCode == 27) {
			event.preventDefault();
			top.window.closeDebugSidebar();
		}
	});
}
