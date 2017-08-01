window.onload = function() {
	var inputElements = document.getElementsByName('debugtoolbar-ids[]');
	if (inputElements.length < 1) {
		console.info('no debug ids found');
		return;
	}
	
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
	
	var sidebarElement = document.createElement('div');
	sidebarElement.id  = 'debugtoolbar-sidebar';
	
	var iframeElement = document.createElement('iframe');
	iframeElement.src = iframeSource;
	iframeElement.id  = 'debugtoolbar-iframe';
	
	var toggleElement = document.createElement('div');
	toggleElement.id = 'debugtoolbar-toggler';
	
	var toggleInnerElement = document.createElement('div');
	toggleInnerElement.id        = 'debugtoolbar-toggler-inner';
	toggleInnerElement.innerHTML = '&lsaquo;';
	toggleElement.appendChild(toggleInnerElement);
	
	// prevent FOUC
	setTimeout(function() {
		containerElement.appendChild(sidebarElement);
		containerElement.appendChild(toggleElement);
	}, 250);
	
	/**
	 * shortcuts
	 * - Shift+D: open/close the sidebar
	 * - Escape:  close the sidebar
	 */
	window.addEventListener('keydown', function(event) {
		if (event.ctrlKey == false && event.altKey == false && event.shiftKey && event.keyCode == 68) {
			event.preventDefault();
			toggleDebugSidebar();
		}
		if (event.ctrlKey == false && event.altKey == false && event.shiftKey == false && event.keyCode == 27 && toggleElement.className == 'debugtoolbar-toggler-active') {
			event.preventDefault();
			closeDebugSidebar();
		}
	});
	
	/**
	 * open/close the iframe
	 */
	window.toggleDebugSidebar = function() {
		if (toggleElement.className == 'debugtoolbar-toggler-active') {
			closeDebugSidebar();
		}
		else {
			openDebugSidebar();
		}
	};
	window.openDebugSidebar = function() {
		if (document.getElementById(iframeElement.id) == null) {
			sidebarElement.appendChild(iframeElement);
		}
		
		toggleElement.className = 'debugtoolbar-toggler-active';
		sidebarElement.className = 'debugtoolbar-sidebar-show';
		toggleInnerElement.innerHTML = '&rsaquo;';
	};
	window.closeDebugSidebar = function() {
		toggleElement.className = '';
		sidebarElement.className = '';
		toggleInnerElement.innerHTML = '&lsaquo;';
		iframeElement.blur();
	};
	toggleElement.addEventListener('click', toggleDebugSidebar, false);
}
