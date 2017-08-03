var toggleDebugDetail = function(partName, detailKey, detailMode) {
	if (detailMode != 'inline' && top.window.extendDebugDisplay != undefined) {
		top.window.extendDebugDisplay();
	}
	
	if (detailMode == 'inline') {
		var $detailLink = $('[data-part-name="' + partName + '"][data-detail-key="' + detailKey + '"]');
		var $detailContainer = $detailLink.next('.detail-container');
		
		if ($detailContainer.length > 0) {
			$detailContainer.toggleClass('detail-active');
		}
		else {
			loadDebugDetail(partName, detailKey, detailMode, function(data) {
				$detailContainer = $('<div class="detail-container detail-active"></div>');
				$detailContainer.html(data);
				$detailLink.after($detailContainer);
			});
		}
	}
	else {
		loadDebugDetail(partName, detailKey, detailMode, function(data) {
			$('#detail-container').addClass('detail-active');
			$('#detail-container').html(data);
		});
	}
}

var loadDebugDetail = function(partName, detailKey, detailMode, callback) {
	/**
	 * convert to a single detail key to pass to the backend
	 * this keeps the backend simple, which needs manual implementation
	 */
	var detailKey = partName + '|' + detailKey + '|' + detailMode;
	var url       = window.location.href + '&detail=' + detailKey;
	
	$.get(url).done(function(data, textStatus, jqXHR){
		callback(data);
	});
}
