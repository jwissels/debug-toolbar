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
	
	var sidebarElement = document.createElement('div');
	sidebarElement.id  = 'debug-toolbar-sidebar';
	
	var iframeElement = document.createElement('iframe');
	iframeElement.src = iframeSource;
	iframeElement.id  = 'debug-toolbar-iframe';
	
	var toggleElement = document.createElement('div');
	toggleElement.id = 'debug-toolbar-toggler';
	
	var toggleInnerElement = document.createElement('div');
	toggleInnerElement.id        = 'debug-toolbar-toggler-inner';
	toggleInnerElement.innerHTML = '&lsaquo;';
	toggleElement.appendChild(toggleInnerElement);
	
	// prevent FOUC
	setTimeout(function() {
		containerElement.appendChild(sidebarElement);
		containerElement.appendChild(toggleElement);
	}, 250);
	
	/**
	 * shortcuts
	 * - Shift+D: open/close the toolbar
	 * - Escape:  close the toolbar
	 */
	window.addEventListener('keydown', function(event) {
		if (event.ctrlKey == false && event.altKey == false && event.shiftKey && event.keyCode == 68) {
			event.preventDefault();
			toggleDebugSidebar();
		}
		if (event.ctrlKey == false && event.altKey == false && event.shiftKey == false && event.keyCode == 27 && toggleElement.className == 'debug-toolbar-toggler-active') {
			event.preventDefault();
			closeDebugSidebar();
		}
	});
	
	/**
	 * open/close the iframe
	 */
	window.toggleDebugSidebar = function() {
		if (toggleElement.className == 'debug-toolbar-toggler-active') {
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
		
		toggleElement.className = 'debug-toolbar-toggler-active';
		sidebarElement.className = 'debug-toolbar-sidebar-show';
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
