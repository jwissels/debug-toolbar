window.onload = function() {
	/**
	 * make accordion headings clickable, not only the link inside them
	 */
	var accordionTitles = document.getElementsByClassName('panel-heading');
	var accordionTitle  = null;
	var accordionLink   = null;
	for (var i=0; i<accordionTitles.length; i++) {
		accordionTitle = accordionTitles[i];
		accordionTitle.className = accordionTitle.className + ' clickable';
		accordionTitle.addEventListener('click', function(event) {
			accordionLink = this.querySelectorAll('[data-toggle="collapse"]')[0];
			accordionLink.click();
		});
	}
	
	/**
	 * open the detail
	 */
	var detailLinks = document.querySelectorAll('[data-detail="true"]');
	var detailLink  = null;
	for (var i=0; i<detailLinks.length; i++) {
		detailLink = detailLinks[i];
		detailLink.addEventListener('click', function(event) {
			event.preventDefault();
			toggleDebugDetail(this.dataset.partName, this.dataset.detailKey, this.dataset.detailMode);
		});
	}
}
