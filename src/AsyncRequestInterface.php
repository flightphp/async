<?php

declare(strict_types=1);

namespace flight;

interface AsyncRequestInterface
{
    /**
     * This will get the underlying request for an async service
     *
     * @return mixed
     */
    public function getRequest();

    /**
     * Gets the $_SERVER vars
     *
     * @return array<string, mixed>
     */
    public function getServer(): ?array;

    /**
     * Gets the headers
     *
     * @return array<string, string>
     */
    public function getHeaders(): ?array;

    /**
     * Get HTTP Headers
     *
     * @return array<string, string>
     */
    public function getCookies(): ?array;

    /**
     * Gets $_GET
     *
     * @return array<string, string>
     */
    public function getGet(): ?array;

    /**
     * Gets $_POST
     *
     * @return array<string, string>
     */
    public function getPost(): ?array;

    /**
     * Gets $_FILES
     *
     * @return array<mixed, mixed>
     */
    public function getFiles(): ?array;

    /**
     * Request Body
     *
     * @return string
     */
    public function getBody(): ?string;
}
