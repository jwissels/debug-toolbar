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
	
	/**
	 * open the detail
	 */
	var detailLinks = document.querySelectorAll('[data-detail-key]');
	for (var i=0; i<detailLinks.length; i++) {
		var detailLink = detailLinks[i];
		var detailKey  = detailLink.dataset.detailKey;
		detailLink.addEventListener('click', function(event) {
			event.preventDefault();
			if (top.window.extendDebugDisplay != undefined) {
				top.window.extendDebugDisplay(detailKey);
			}
			showDebugDetail(detailKey);
		});
	}
}
