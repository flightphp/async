<?php

declare(strict_types=1);

namespace flight;

interface AsyncResponseInterface
{
    /**
     * This will get the underlying request for an async service
     *
     * @return mixed
     */
    public function getResponse();

    /**
     * Sets a particular header
     *
     * @param string $header - the field for the header like Content-Type
     * @param mixed  $value  - Whatever value to set
     *
     * @return void
     */
    public function setHeader(string $header, $value): void;

    /**
     * Sets the status code
     *
     * @param integer $status_code status_code
     *
     * @return void
     */
    public function setHttpStatus(int $status_code): void;

    /**
     * Sets the response body
     *
     * @param string $body body
     *
     * @return void
     */
    public function setBody(string $body): void;
}
