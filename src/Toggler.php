<?php

namespace alsvanzelf\debugtoolbar;

class Toggler {
	private $logId;
	
	private $scriptUrl;
	
	private $detailUrl;
	
	public function __construct($logId, $scriptUrl='/dist/debug-toggler.js', $detailUrl='/debug-toolbar.php?logId={logId}') {
		$this->logId     = $logId;
		$this->scriptUrl = $scriptUrl;
		$this->detailUrl = $detailUrl;
	}
	
	public function render() {
		return PHP_EOL.$this->scriptTag().PHP_EOL.$this->idTag().PHP_EOL;
	}
	
	public function scriptTag() {
		return '<script src="'.$this->scriptUrl.'" id="debug-toolbar-script" data-url="'.$this->detailUrl.'"></script>';
	}
	
	public function idTag() {
		return '<input type="hidden" name="debug-toolbar-ids[]" value="'.$this->logId.'">';
	}
}
