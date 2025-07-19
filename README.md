# What is Async?

Async is a package for the Flight framework that allows you to run your Flight apps in asynchronous frameworks like ReactPHP, Swoole, Amp, RoadRunner, Workerman, Adapterman, etc. Currently only configured for [Swoole](https://www.swoole.co.uk/) and [AdapterMan](https://github.com/joanhey/AdapterMan)

# Requirements

- PHP 7.4 or higher
- Swoole extension (for Swoole usage)
- Flight framework 3.16.1 or higher

# Installation

Installation is done through composer.

```bash
composer require flightphp/async
```

For Swoole support, you'll also need to install the Swoole extension:

```bash
# Using PECL
pecl install swoole

# Or using package manager (Ubuntu/Debian)
sudo apt-get install php-swoole
```

# Basic Usage (swoole)

Here is a simple example of how to use Flight Async where you can use Swoole or PHP-FPM as the server driver within the same project. This allows for you to develop your application using the PHP-FPM driver for better debugging and then switch to Swoole for production.

## Setup
First you'll need the following files in your project:

### index.php
```php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```
### swoole_server.php
```php
<?php

// ./swoole_server.php file

require_once(__DIR__.'/vendor/autoload.php');

$app = Flight::app();

$app->route('/', function() use ($app) {
    $app->json([
        'hello' => 'world'
    ]);
});

if(!defined("NOT_SWOOLE")) {
    // Require the SwooleServerDriver class since we're running in Swoole mode.
    require_once(__DIR__.'/SwooleServerDriver.php');

    // Custom little hack
    // Makes it so the app doesn't stop when it runs.
    $app->map('stop', function (?int $code = null) use ($app) {
        if ($code !== null) {
            $app->response()->status($code);
        }
    });
    Swoole\Runtime::enableCoroutine();
    $Swoole_Server = new SwooleServerDriver('127.0.0.1', 9501, $app);
    $Swoole_Server->start();
} else {
    $app->start();
}
```

### SwooleServerDriver.php
```php
<?php

use flight\adapter\SwooleAsyncRequest;
use flight\adapter\SwooleAsyncResponse;
use flight\AsyncBridge;
use flight\Engine;
use Swoole\HTTP\Server as SwooleServer;
use Swoole\HTTP\Request as SwooleRequest;
use Swoole\HTTP\Response as SwooleResponse;

class SwooleServerDriver {

    //use ConnectionPoolTrait;

    /** @var SwooleServer */
    protected $Swoole;

    /** @var Engine */
    protected $app;

    public function __construct(string $host, int $port, Engine $app) {
        $this->Swoole = new SwooleServer($host, $port);
        $this->app = $app;

        $this->setDefault();
        $this->bindWorkerEvents();
        $this->bindHttpEvent();
    }

    protected function setDefault() {
        // A bunch of default settings for the Swoole server.
        // You can customize these settings based on your needs.
        $this->Swoole->set([
            'daemonize'             => false,
            'dispatch_mode'         => 1,
            'max_request'           => 8000,
            'open_tcp_nodelay'      => true,
            'reload_async'          => true,
            'max_wait_time'         => 60,
            'enable_reuse_port'     => true,
            'enable_coroutine'      => true,
            'http_compression'      => false,
            'enable_static_handler' => false,
            'buffer_output_size'    => 4 * 1024 * 1024,
            'worker_num'            => 4, // Each worker holds a connection pool
        ]);
    }

    protected function bindHttpEvent() {
        $app = $this->app;
        $AsyncBridge = new AsyncBridge($app);
        $this->Swoole->on("Start", function(SwooleServer $server) {
            echo "Swoole http server is started at http://127.0.0.1:9501\n";
        });

        // This is where the magic happens, the request is processed by the AsyncBridge
        $this->Swoole->on('Request', function (SwooleRequest $request, SwooleResponse $response) use ($AsyncBridge) {
            $SwooleAsyncRequest = new SwooleAsyncRequest($request);
            $SwooleAsyncResponse = new SwooleAsyncResponse($response);
            $AsyncBridge->processRequest($SwooleAsyncRequest, $SwooleAsyncResponse);
            $response->end();

            gc_collect_cycles(); // Collect garbage to free memory (optional)
        });
    }

    protected function bindWorkerEvents() {
        // You can use this to set custom events for the workers, such as creating connection pools.
        $createPools = function() {
            // Create connection pools for each worker
            // This is useful for managing database connections or other resources that need to be shared across requests.
        };
        $closePools = function() {
            // Close connection pools for each worker
            // This is useful for cleaning up resources when the worker stops or encounters an error.
        };
        $this->Swoole->on('WorkerStart', $createPools);
        $this->Swoole->on('WorkerStop', $closePools);
        $this->Swoole->on('WorkerError', $closePools);
    }

    public function start() {
        $this->Swoole->start();
    }
}
```

## Running the server

### Development Mode (PHP Built-in Server)
To run the server with PHP-FPM for development, you can use the following command in your terminal:
```bash
php -S localhost:8000 # or add -t public/ if you have your index file in the public directory
```

### Production Mode (Swoole)
When you are ready for production, you can switch to Swoole by running the following command:
```bash
php swoole_server.php
```

### Daemonizing the Swoole Server
If you want the Swoole server to run in the background (daemonized), you can modify the configuration in the `SwooleServerDriver.php` file by changing:

```php
'daemonize' => true,
```

**Note:** Swoole runs as its own process and is not a full-fledged web server like Apache or Nginx. You'll likely want to use a reverse proxy with Nginx or Apache to handle SSL termination, load balancing, and serving static files.

## Configuration

The `SwooleServerDriver` class includes several configuration options that you can customize:

- `worker_num`: Number of worker processes (default: 4)
- `max_request`: Maximum requests per worker before restart (default: 8000)
- `enable_coroutine`: Enable coroutines for better concurrency (default: true)
- `buffer_output_size`: Output buffer size (default: 4MB)

## Error Handling

The AsyncBridge handles Flight exceptions and converts them to appropriate HTTP responses. You can also add custom error handling in your routes:

```php
$app->route('/*', function() use ($app) {
    try {
        // Your route logic here
    } catch (Exception $e) {
        $app->response()->status(500);
        $app->json(['error' => $e->getMessage()]);
    }
});
```

# License

Flight Async is released under the [MIT](http://docs.flightphp.com/license) license.
