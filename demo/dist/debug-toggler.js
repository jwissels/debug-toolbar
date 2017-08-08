window.onload = function() {
	var inputElements = document.getElementsByName('debugtoolbar-ids[]');
	if (inputElements.length < 1) {
		console.info('no debug ids found');
		return;
	}
	
	/**
	 * bootstrap
	 */
	var scriptElement = document.getElementById('debugtoolbar-script');
	var styleSource   = scriptElement.src.replace(/\.js/, '.css');
	var iframeSource  = scriptElement.dataset.url.replace(/\{logId\}/, inputElements[0].value);
	
	var containerElement = document.createElement('div');
	containerElement.id = 'debugtoolbar-container';
	document.body.appendChild(containerElement);
	
	var styleElement = document.createElement('link');
	styleElement.rel  = 'stylesheet';
	styleElement.href = styleSource;
	containerElement.appendChild(styleElement);
	
	/**
	 * toggler & display
	 */
	var toggleElement = document.createElement('div');
	toggleElement.id = 'debugtoolbar-toggler';
	
	var toggleInnerElement = document.createElement('div');
	toggleInnerElement.id        = 'debugtoolbar-toggler-inner';
	toggleInnerElement.innerHTML = '&lsaquo;';
	toggleElement.appendChild(toggleInnerElement);
	
	var displayElement = document.createElement('div');
	displayElement.id = 'debugtoolbar-display';
	
	var displayIframeElement = document.createElement('iframe');
	displayIframeElement.src = iframeSource;
	displayIframeElement.id  = 'debugtoolbar-display-iframe';
	
	// prevent FOUC
	setTimeout(function() {
		containerElement.appendChild(displayElement);
		containerElement.appendChild(toggleElement);
	}, 250);
	
	/**
	 * shortcuts
	 * - Shift+D: open/close display
	 * - Escape:  close display
	 */
	window.addEventListener('keydown', function(event) {
		if (event.ctrlKey == false && event.altKey == false && event.shiftKey && event.keyCode == 68) {
			event.preventDefault();
			toggleDebugDisplay();
		}
		if (event.ctrlKey == false && event.altKey == false && event.shiftKey == false && event.keyCode == 27 && toggleElement.classList.contains('debugtoolbar-toggler-active')) {
			event.preventDefault();
			closeDebugDisplay();
		}
	});
	
	/**
	 * open/extend/close display
	 */
	window.toggleDebugDisplay = function() {
		if (toggleElement.classList.contains('debugtoolbar-toggler-active')) {
			closeDebugDisplay();
		}
		else {
			openDebugDisplay();
		}
	};
	window.openDebugDisplay = function() {
		if (document.getElementById(displayIframeElement.id) == null) {
			displayElement.appendChild(displayIframeElement);
		}
		
		toggleElement.classList.add('debugtoolbar-toggler-active');
		toggleElement.classList.add('debugtoolbar-toggler-sidebar');
		displayElement.classList.add('debugtoolbar-display-active');
		displayElement.classList.add('debugtoolbar-display-sidebar');
		toggleInnerElement.innerHTML = '&rsaquo;';
	};
	window.extendDebugDisplay = function() {
		toggleElement.classList.add('debugtoolbar-toggler-detail');
		toggleElement.classList.remove('debugtoolbar-toggler-sidebar');
		displayElement.classList.add('debugtoolbar-display-detail');
		displayElement.classList.remove('debugtoolbar-display-sidebar');
	}
	window.closeDebugDisplay = function() {
		displayElement.classList.remove('debugtoolbar-display-active');
		displayElement.classList.remove('debugtoolbar-display-sidebar');
		displayElement.classList.remove('debugtoolbar-display-detail');
		toggleElement.classList.remove('debugtoolbar-toggler-active');
		toggleElement.classList.remove('debugtoolbar-toggler-sidebar');
		toggleElement.classList.remove('debugtoolbar-toggler-detail');
		toggleInnerElement.innerHTML = '&lsaquo;';
		displayIframeElement.blur();
		iframeCloseDebugCallback();
	};
	toggleElement.addEventListener('click', toggleDebugDisplay, false);
	
	window.iframeCloseDebugCallback = function() {};
}
