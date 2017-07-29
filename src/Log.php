<?php

namespace alsvanzelf\debugtoolbar;

use alsvanzelf\debugtoolbar\processors\RequestTimeProcessor;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

class Log {
	private static $logId = null;
	
	public static function handle(LoggerInterface $logger) {
		self::$logId = uniqid();
		
		/**
		 * request processors
		 */
		$logger->pushProcessor(new GitProcessor());
		$logger->pushProcessor(new MemoryPeakUsageProcessor());
		$logger->pushProcessor(new MemoryUsageProcessor());
		$logger->pushProcessor(new RequestTimeProcessor());
		$logger->pushProcessor(new WebProcessor());
		
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
