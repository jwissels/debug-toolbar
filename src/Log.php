<?php

namespace alsvanzelf\debugtoolbar;

use Psr\Log\LoggerInterface;

class Log {
	private static $logId = null;
	
	public static function handle(LoggerInterface $logger) {
		self::$logId = uniqid();
		
		$context = [
			'logId' => self::$logId,
		];
		$logger->debug('debug request', $context);
		
		return self::$logId;
	}
}
