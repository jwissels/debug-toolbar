window.onload = function() {
	/**
	 * make accordion headings clickable, not only the link inside them
	 */
	var accordionTitles = document.getElementsByClassName('panel-heading');
	for (var i=0; i<accordionTitles.length; i++) {
		var accordionTitle = accordionTitles[i];
		accordionTitle.className = accordionTitle.className + ' clickable';
		accordionTitle.addEventListener('click', function(event) {
			var accordionLink = accordionTitle.querySelectorAll('[data-toggle="collapse"]')[0];
			accordionLink.click();
		});
	}
	
	/**
	 * shortcuts
	 * - Shift+D: open/close the toolbar
	 * - Escape:  close the toolbar
	 */
	window.addEventListener('keydown', function(event) {
		if (event.shiftKey && event.keyCode == 68) {
			event.preventDefault();
			top.window.closeDebugDetails();
		}
		if (event.keyCode == 27) {
			event.preventDefault();
			top.window.closeDebugDetails();
		}
	});
}
