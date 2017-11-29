<?php

namespace alsvanzelf\debugtoolbar;

use alsvanzelf\debugtoolbar\parts\PDOPart;
use alsvanzelf\debugtoolbar\parts\RequestPart;
use alsvanzelf\debugtoolbar\parts\TwigPart;
use Psr\Log\LoggerInterface;

class Log {
	private static $logId = null;
	
	public static function track(LoggerInterface $logger) {
		self::$logId = !empty($_SERVER['UNIQUE_ID']) ? $_SERVER['UNIQUE_ID'] : bin2hex(random_bytes(16));
		
		$logger->pushProcessor(function(array $record) {
			$record['extra']['request'] = RequestPart::track();
			$record['extra']['pdo']     = PDOPart::track();
			$record['extra']['twig']    = TwigPart::track();
			
			return $record;
		});
		
		/**
		 * log
		 */
		$context = [
			'logId' => self::$logId,
		];
		$logger->debug('debug request', $context);
		
		return self::$logId;
	}
}
