<?php

use alsvanzelf\debugtoolbar\Log;
use alsvanzelf\debugtoolbar\Toggler;
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

usleep(100);

/**
 * end of the request
 */

// prepare logger, deciding how to store the log
$logger = new Logger($channel='demo');
$logger->pushHandler(new MongoDBHandler(new Client('mongodb://127.0.0.1:27017'), $database='demo', $collection='debug'));

// store the requests data in the log
$logId = Log::handle($logger);

/**
 * render the toggler in the template
 */
$toggler = new Toggler($logId);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Demo</title>
	<style type="text/css">
		body {
			background-color: lightblue;
		}
	</style>
</head>
<body>
	<h1>Demo</h1>
	<?php echo $toggler->render(); ?>
</body>
</html>
