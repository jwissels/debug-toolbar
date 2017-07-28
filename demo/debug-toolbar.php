<?php

use alsvanzelf\debugtoolbar\Display;
use MongoDB\Client;

/**
 * boostrap
 */
require_once __DIR__.'/../vendor/autoload.php';
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

/**
 * render detail of the log
 */
$mongo = new Client('mongodb://127.0.0.1:27017');
$cursor = $mongo->demo->debug->find($query=['context.logId' => $_GET['logId']]);
$results = $cursor->toArray();

$detail = new Display($results[0]);
echo $detail->renderLog();
