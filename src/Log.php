<?php

namespace alsvanzelf\debugtoolbar;

use alsvanzelf\debugtoolbar\parts\PDOPart;
use alsvanzelf\debugtoolbar\parts\RequestPart;
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
	
	public static function track(LoggerInterface $logger) {
		self::$logId = uniqid();
		
		$logger->pushProcessor(function(array $record) {
			$record['extra']['request'] = RequestPart::track();
			$record['extra']['pdo']     = PDOPart::track();
			
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
