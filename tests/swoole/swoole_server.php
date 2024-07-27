<?php

// ./swoole_server.php file

require_once(__DIR__.'/../../vendor/autoload.php');
require_once(__DIR__.'/SwooleServerDriver.php');

$app = Flight::app();

$app->route('/', function() use ($app) {
	$app->json([
		'hello' => 'world'
	]);
});

$app->route('/r', function() use ($app) {
	$app->json([
		'something' => 'else'
	]);
});

$app->route('/e', function() use ($app) {
	$app->jsonHalt([
		'i' => 'else'
	]);
});

$app->route('/t', function() use ($app) {
	echo '<h1>Test</h1>';
});

// Makes it so the app doesn't stop when it runs.
$app->map('stop', function (?int $code = null) use ($app) {
	if ($code !== null) {
		$app->response()->status($code);
	}
});

if(!defined("NOT_SWOOLE")) {
	// $app->map('stop', function() use ($app) {
	// 	$response = $app->response();

    //     if (!$response->sent()) {
    //         // if (null !== $code) {
    //         //     $response->status($code);
    //         // }

    //         $content = ob_get_clean();
    //         $response->write($content ?: '');

    //         //$response->send();
    //     }
	// });

	Swoole\Runtime::enableCoroutine();
	$Swoole_Server = new SwooleServerDriver('127.0.0.1', 9501, $app);
	$Swoole_Server->start();
} else {
	$app->start();
}