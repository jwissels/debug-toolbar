window.onload = function() {
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
}
