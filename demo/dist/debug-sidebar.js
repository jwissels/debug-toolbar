window.onload = function() {
	/**
	 * make accordion headings clickable, not only the link inside them
	 */
	var accordionTitles = document.getElementsByClassName('panel-heading');
	Array.prototype.forEach.call(accordionTitles, function(accordionTitle) {
		accordionTitle.classList.add('clickable');
		accordionTitle.addEventListener('click', function(event) {
			var accordionLink = this.querySelectorAll('[data-toggle="collapse"]')[0];
			accordionLink.click();
		});
	});
	
	/**
	 * open the detail
	 */
	var detailLinks = document.querySelectorAll('[data-detail="true"]');
	Array.prototype.forEach.call(detailLinks, function(detailLink) {
		detailLink.addEventListener('click', function(event) {
			event.preventDefault();
			toggleDebugDetail(this.dataset.partName, this.dataset.detailKey, this.dataset.detailMode);
		});
	});
}
