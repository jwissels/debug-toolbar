<?php

use alsvanzelf\debugtoolbar\Log;
use alsvanzelf\debugtoolbar\Toggler;
use alsvanzelf\debugtoolbar\parts\PDOPart;
use alsvanzelf\debugtoolbar\parts\TwigPart;
use MongoDB\Client;
use Monolog\Logger;
use Monolog\Handler\MongoDBHandler;

/**
 * boostrap
 */
require_once __DIR__.'/../vendor/autoload.php';
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

/**
 * run your application
 */

try {
	// supply working credentials + table to see PDO debugging
	$pdo = new \PDO('mysql:host=127.0.0.1;dbname=debug_demo', 'debug_demo', 'debug_demo');
	$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	
	function executePDO($pdo, $query, $binds=[]) {
		$statement = $pdo->prepare($query);
		foreach ($binds as $key => $value) {
			$statement->bindValue($key, $value);
		}
		$statement->execute();
		
		// pass every executed statement
		PDOPart::trackExecutedStatement($statement, $binds);
		
		$record = $statement->fetch(\PDO::FETCH_ASSOC);
		$statement->closeCursor();
		return $record;
	}
	
	$posts = [];
	$posts[] = executePDO($pdo, "SELECT * FROM `posts` WHERE `id` = :id;", ['id'=>1]);
	$posts[] = executePDO($pdo, "SELECT * FROM `posts` WHERE `id` = :id;", ['id'=>1]);
	$posts[] = executePDO($pdo, "SELECT * FROM `posts` WHERE `id` = :id;", ['id'=>2]);
	$posts[] = executePDO($pdo, "SELECT * FROM `posts` WHERE `id` = :id;", ['id'=>3]);
	$posts[] = executePDO($pdo, "SELECT * FROM `posts` WHERE `title` = :title AND `active` = :active;", ['title'=>'Foo', 'active'=>1]);
}
catch (\Exception $e) {
	// continue if sql is not configured
}

$loader  = new \Twig_Loader_Filesystem([__DIR__.'/templates']);
$options = ['debug' => true];
$twig    = new \Twig_Environment($loader, $options);

if ($twig->isDebug()) {
	$profiler = new \Twig_Profiler_Profile();
	
	// pass the profiler once during twig setup
	TwigPart::trackProfiler($profiler);
	
	$twig->addExtension(new \Twig_Extension_Profiler($profiler));
}

$pageData = [];
if (!empty($posts)) {
	$pageData['posts'] = $posts;
}
$pageRendered = $twig->render('index.html', $pageData);

/**
 * end of the request
 */

// prepare logger, deciding how to store the log
$logger = new Logger($channel='demo');
$logger->pushHandler(new MongoDBHandler(new Client('mongodb://127.0.0.1:27017'), $database='demo', $collection='debug'));

// store the requests data in the log
$logId = Log::track($logger);

/**
 * render the toggler in the template
 */
$pageRendered = str_replace('</body>', (new Toggler($logId))->render().'</body>', $pageRendered);

echo $pageRendered;
