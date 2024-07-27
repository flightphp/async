<?php

use flight\adapter\SwooleAsyncRequest;
use flight\adapter\SwooleAsyncResponse;
use flight\AsyncBridge;
use flight\Engine;
use Smf\ConnectionPool\ConnectionPool;
use Smf\ConnectionPool\ConnectionPoolTrait;
use Smf\ConnectionPool\Connectors\CoroutineMySQLConnector;
use Swoole\HTTP\Server as SwooleServer;
use Swoole\HTTP\Request as SwooleRequest;
use Swoole\HTTP\Response as SwooleResponse;
use Swoole\Coroutine\MySQL;

class SwooleServerDriver {

	//use ConnectionPoolTrait;

	/** @var SwooleServer */
    protected $Swoole;

	/** @var Engine */
	protected $app;

	/** @var Async_Bridge */
	protected $Async_Bridge;

    public function __construct(string $host, int $port, Engine $app) {
        $this->Swoole = new SwooleServer($host, $port);
		$this->app = $app;

        $this->setDefault();
        $this->bindWorkerEvents();
        $this->bindHttpEvent();
    }

	protected function setDefault() {
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

        $this->Swoole->on('Request', function (SwooleRequest $request, SwooleResponse $response) use ($AsyncBridge) {
			echo memory_get_peak_usage() . PHP_EOL;
			// $pool1 = $this->getConnectionPool('mysql');
            // // /**@var MySQL $mysql */
            // $pdo = $pool1->borrow();
			
			// $server_vars = $request->server;
			// $server_vars = array_change_key_case($server_vars, CASE_UPPER);
			// $_SERVER = $server_vars;
			// $_GET = $request->get ?? [];
			// $_POST = $request->post ?? [];
			// $_COOKIE = $request->cookie ?? [];
			// $_FILES = $request->files ?? [];
			// $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

			// $Engine->start();

			// foreach($Engine->response()->headers() as $header => $value) {
			// 	$response->header($header, $value);
			// }

			// $response->status($Engine->response()->status());
			// if($Engine->response()->getBody()) {
			// 	$response->write($Engine->response()->getBody());
			// }

			// $Engine->unregister('response');
			// $Engine->unregister('request');
			// $Engine->register('response', 'flight\net\Response');
			// $Engine->register('request', 'flight\net\Request');

			$SwooleAsyncRequest = new SwooleAsyncRequest($request);
			$SwooleAsyncResponse = new SwooleAsyncResponse($response);
			$AsyncBridge->processRequest($SwooleAsyncRequest, $SwooleAsyncResponse);
			// $pool1->return($pdo);
			$response->end();
        });
    }

    protected function bindWorkerEvents() {
        $createPools = function () {

			// $database_config = $this->App->getServicesContainer()->Config->database;
            // // All MySQL connections: [4 workers * 2 = 8, 4 workers * 10 = 40]
            // $pool1 = new ConnectionPool(
            //     [
            //         'minActive' => 20,
            //         'maxActive' => 100,
            //     ],
            //     new CoroutineMySQLConnector,
            //     [
            //         'host'        => $database_config['host'],
            //         'port'        => $database_config['port'],
            //         'user'        => $database_config['username'],
            //         'password'    => $database_config['password'],
            //         'database'    => $database_config['dbname'],
            //         'timeout'     => 10,
            //         'charset'     => 'utf8mb4',
            //         'strict_type' => true,
            //         'fetch_mode'  => true,
            //     ]);
            // $pool1->init();
            // $this->addConnectionPool('mysql', $pool1);
        };
        $closePools = function () {
            // $this->closeConnectionPools();
        };
        $this->Swoole->on('WorkerStart', $createPools);
        $this->Swoole->on('WorkerStop', $closePools);
        $this->Swoole->on('WorkerError', $closePools);
    }

    public function start()
    {
        $this->Swoole->start();
    }
}