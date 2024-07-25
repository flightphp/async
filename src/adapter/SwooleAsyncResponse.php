<?php

declare(strict_types=1);

namespace flight\adapter;

use flight\AsyncResponseInterface;
use Swoole\Http\Response as Swoole_Response;

class SwooleAsyncResponse implements AsyncResponseInterface
{
    /**
     * Swoole Response
     *
     * @var Swoole_Response
     */
    protected $Response;

    /**
     * Construct
     *
     * @param Swoole_Response $Swoole_Response - Swoole Response object
     */
    public function __construct($Swoole_Response)
    {
        $this->Response = $Swoole_Response;
    }

    /**
     * Gets the swoole response object
     *
     * @return Swoole_Response
     */
    public function getResponse()
    {
        return $this->Response;
    }

    /**
     * Sets a particular header
     *
     * @param string $header - the field for the header like Content-Type
     * @param mixed  $value  - Whatever value to set
     *
     * @return void
     */
    public function setHeader(string $header, $value): void
    {
        $this->Response->header($header, $value);
    }

    /**
     * Sets the status code
     *
     * @param integer $status_code status_code
     *
     * @return void
     */
    public function setHttpStatus(int $status_code): void
    {
        $this->Response->status($status_code);
    }

    /**
     * Sets the response body
     *
     * @param string $body body
     *
     * @return void
     */
    public function setBody(string $body): void
    {
        $this->Response->write($body);
    }
}
