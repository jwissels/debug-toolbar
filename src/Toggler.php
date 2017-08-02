<?php

namespace alsvanzelf\debugtoolbar;

class Toggler {
	private $logId;
	
	private $scriptUrl;
	
	private $displayUrl;
	
	public function __construct($logId, $scriptUrl='/dist/debug-toggler.js', $displayUrl='/debug-display.php?logId={logId}') {
		$this->logId     = $logId;
		$this->scriptUrl = $scriptUrl;
		$this->displayUrl = $displayUrl;
	}
	
	public function render() {
		return PHP_EOL.$this->scriptTag().PHP_EOL.$this->idTag().PHP_EOL;
	}
	
	public function scriptTag() {
		return '<script src="'.$this->scriptUrl.'" id="debugtoolbar-script" data-url="'.$this->displayUrl.'"></script>';
	}
	
	public function idTag() {
		return '<input type="hidden" name="debugtoolbar-ids[]" value="'.$this->logId.'">';
	}
}
