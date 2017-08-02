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
	var detailLinks = document.querySelectorAll('[data-detail="true"]');
	for (var i=0; i<detailLinks.length; i++) {
		var detailLink = detailLinks[i];
		var partName   = detailLink.dataset.partName;
		var detailKey  = detailLink.dataset.detailKey;
		var detailMode = detailLink.dataset.detailMode;
		detailLink.addEventListener('click', function(event) {
			event.preventDefault();
			toggleDebugDetail(partName, detailKey, detailMode);
		});
	}
}
