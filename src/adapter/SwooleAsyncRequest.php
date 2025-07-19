<?php

declare(strict_types=1);

namespace flight\adapter;

use flight\AsyncRequestInterface;
use Swoole\Http\Request as Swoole_Request;

class SwooleAsyncRequest implements AsyncRequestInterface
{
    /**
     * Swoole request
     *
     * @var Swoole_Request
     */
    public $Request;

    /**
     * Construct
     *
     * @param Swoole_Request $Swoole_Request - the swoole request object
     */
    public function __construct($Swoole_Request)
    {
        $this->Request = $Swoole_Request;
    }

    /**
     * Gets the swoole request object
     *
     * @return Swoole_Request
     */
    public function getRequest()
    {
        return $this->Request;
    }

    /**
     * Get $_SERVER vars
     *
     * @return array<string, mixed>
     */
    public function getServer(): ?array
    {
        return $this->Request->server;
    }

    /**
     * Get HTTP Headers
     *
     * @return array<string, string>
     */
    public function getHeaders(): ?array
    {
        return $this->Request->header;
    }

    /**
     * Get HTTP Headers
     *
     * @return array<string, string>
     */
    public function getCookies(): ?array
    {
        return $this->Request->cookie;
    }

    /**
     * Get $_GET
     *
     * @return array<string, string>
     */
    public function getGet(): ?array
    {
        return $this->Request->get;
    }

    /**
     * Get $_POST
     *
     * @return array<string, string>
     */
    public function getPost(): ?array
    {
        return $this->Request->post;
    }

    /**
     * Get $_FILES
     *
     * @return array<mixed, mixed>
     */
    public function getFiles(): ?array
    {
        return $this->Request->files;
    }

    /**
     * Gets the request body (if it's a POST or PUT)
     *
     * @return string
     */
    public function getBody(): ?string
    {
        return $this->Request->rawContent();
    }
}
