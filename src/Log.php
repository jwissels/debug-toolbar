<?php

namespace alsvanzelf\debugtoolbar;

use alsvanzelf\debugtoolbar\processors\PDOQueryProcessor;
use alsvanzelf\debugtoolbar\processors\RequestTimeProcessor;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

class Log {
	public static $trackedPDOQueries = [];
	
	private static $logId = null;
	
	/**
	 * @note should be called after executing a statement
	 *       this allows new features to get most debug information from the statement object
	 * 
	 * @param  \PDOStatement $statement
	 * @param  array         $binds     needed as currently PDOStatement's debugDumpParams() doesn't include bind values
	 *                                  @see https://bugs.php.net/bug.php?id=52384
	 *                                  @see https://github.com/php/php-src/pull/1999
	 */
	public static function trackPDOExecutedStatement(\PDOStatement $statement, array $binds=[]) {
		self::trackPDOQuery($statement->queryString, $binds);
	}
	
	/**
	 * @note prefer to use trackPDOExecutedStatement() is a statement is available
	 * 
	 * @param  string $queryString
	 * @param  array  $binds
	 */
	public static function trackPDOQuery($queryString, array $binds=[]) {
		self::$trackedPDOQueries[] = [
			'query' => $queryString,
			'binds' => $binds,
		];
	}
	
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
		 * PDO processors
		 */
		$logger->pushProcessor(new PDOQueryProcessor());
		
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
