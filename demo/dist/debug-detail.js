var showDebugDetail = function(detailKey) {
	var url = window.location.href + '&detail=' + detailKey;
	$.get(url).done(function(data, textStatus, jqXHR){
		$('#detail-container').html(data);
	});
}
