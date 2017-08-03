<?php

namespace alsvanzelf\debugtoolbar\models;

class Detail {
	const MODE_FULL   = 'full';
	const MODE_INLINE = 'inline';
	
	public $key = '';
	
	public $mode = self::MODE_FULL;
	
	public function __construct($key, $mode=self::MODE_FULL) {
		$this->key  = $key;
		$this->mode = $mode;
	}
}
