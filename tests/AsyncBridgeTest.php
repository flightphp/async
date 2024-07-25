<?php

declare(strict_types=1);

namespace tests;

use flight\Engine;
use PHPUnit\Framework\TestCase;

class AsyncBridgeTest extends TestCase
{
    /**
     * Test the async bridge
     *
     * @return void
     */
    public function testAsyncBridge()
    {
        $Swoole_Request = new class {
            public $server;
            public $header;
            public $cookie;
            public $get;
            public $post;
            public $files;
            public $rawContent;

            public function rawContent()
            {
                return $this->rawContent;
            }
        };

        $Swoole_Response = new class {
            public $header = [];
            public $status;
            public $content;

            public function header($header, $value)
            {
                $this->header[$header] = $value;
            }

            public function status($status)
            {
                $this->status = $status;
            }

            public function write($content)
            {
                $this->content = $content;
            }

            public function end($content)
            {
                $this->content = $content;
            }
        };

        $Swoole_Request->server = [
            'HTTP_HOST' => 'localhost',
            'REQUEST_URI' => '/test',
            'REQUEST_METHOD' => 'GET',
        ];

        $Swoole_Request->header = [
            'Content-Type' => 'application/json',
        ];

        $Swoole_Request->cookie = [
            'cookie' => 'value',
        ];

        $Swoole_Request->get = [
            'get' => 'value',
        ];

        $Swoole_Request->post = [
            'post' => 'value',
        ];

        $Swoole_Request->files = [
            'files' => 'value',
        ];

        $Swoole_Request->rawContent = 'raw content';

        $Swoole_Async_Request = new \flight\adapter\SwooleAsyncRequest($Swoole_Request);
        $Swoole_Async_Response = new \flight\adapter\SwooleAsyncResponse($Swoole_Response);

        $app = new Engine();
        $app->route('GET /test', function () use ($app) {
            $app->json(['test' => 'test']);
        });
        $Bridge = new \flight\AsyncBridge($app);

        $Response = $Bridge->processRequest($Swoole_Async_Request, $Swoole_Async_Response);

        $this->assertInstanceOf(\flight\AsyncResponseInterface::class, $Response);
        $this->assertEquals('application/json; charset=utf-8', $Response->getResponse()->header['content-type']);
        $this->assertEquals(200, $Response->getResponse()->status);
        $this->assertEquals('{"test":"test"}', $Response->getResponse()->content);
    }
}
