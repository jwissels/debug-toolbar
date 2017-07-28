window.onload = function() {
	var inputElements = document.getElementsByName('debug-toolbar-ids[]');
	if (inputElements.length < 1) {
		console.info('no debug ids found');
		return;
	}
	
	var scriptElement = document.getElementById('debug-toolbar-script');
	var styleSource   = scriptElement.src.replace(/\.js/, '.css');
	var iframeSource  = scriptElement.dataset.url.replace(/\{logId\}/, inputElements[0].value);
	
	var containerElement = document.createElement('div');
	containerElement.id = 'debug-toolbar-container';
	document.body.appendChild(containerElement);
	
	var styleElement = document.createElement('link');
	styleElement.rel  = 'stylesheet';
	styleElement.href = styleSource;
	containerElement.appendChild(styleElement);
	
	var iframeContainerElement = document.createElement('div');
	iframeContainerElement.id  = 'debug-toolbar-iframe-container';
	
	var iframeElement = document.createElement('iframe');
	iframeElement.src = iframeSource;
	iframeElement.id  = 'debug-toolbar-iframe';
	iframeContainerElement.appendChild(iframeElement);
	
	var toggleElement = document.createElement('div');
	toggleElement.id = 'debug-toolbar-toggler';
	
	var toggleInnerElement = document.createElement('div');
	toggleInnerElement.id        = 'debug-toolbar-toggler-inner';
	toggleInnerElement.innerHTML = '&lsaquo;';
	toggleElement.appendChild(toggleInnerElement);
	
	toggleElement.addEventListener('click', function(event) {
		if (toggleElement.className == 'debug-toolbar-toggler-active') {
			toggleElement.className = '';
			iframeContainerElement.className = '';
			toggleInnerElement.innerHTML = '&lsaquo;';
		}
		else {
			toggleElement.className = 'debug-toolbar-toggler-active';
			iframeContainerElement.className = 'debug-toolbar-iframe-container-show';
			toggleInnerElement.innerHTML = '&rsaquo;';
		}
	}, false);
	
	// prevent FOUC
	setTimeout(function() {
		containerElement.appendChild(iframeContainerElement);
		containerElement.appendChild(toggleElement);
	}, 250);
}
