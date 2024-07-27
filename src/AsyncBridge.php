<?php

declare(strict_types=1);

namespace flight;

use flight\net\Request;
use flight\net\Response;
use flight\util\Collection;

class AsyncBridge
{
    /**
     * The app itself
     *
     * @var Engine
     */
    protected $app;

    /**
     * Request
     *
     * @var Request
     */
    public $Request;

    /**
     * Construct
     *
     * @param Engine $app - app
     */
    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    /**
     * Processes a request
     *
     * @param AsyncRequestInterface  $AsyncRequest  - AsyncRequestInterface
     * @param AsyncResponseInterface $AsyncResponse - AsyncResponseInterface
     *
     * @return AsyncResponseInterface
     */
    public function processRequest(AsyncRequestInterface $AsyncRequest, AsyncResponseInterface $AsyncResponse): AsyncResponseInterface
    {
        $_SERVER = array_change_key_case($AsyncRequest->getServer(), CASE_UPPER);
        $Request = new Request();

        $Request        = $this->buildFlightRequestFromAsyncRequest($AsyncRequest, $Request);
        $AsyncResponse = $this->buildAsyncResponseFromFlightRequest($Request, $AsyncResponse);
        return $AsyncResponse;
    }

    /**
     * Builds the request from the async request
     *
     * @param AsyncRequestInterface $AsyncRequest - AsyncRequestInterface
     * @param Request                 $Request       - the http request object
     *
     * @return Request
     */
    protected function buildFlightRequestFromAsyncRequest(AsyncRequestInterface $AsyncRequest, Request $Request): Request
    {
        //$Request = $this->copyHeaders($AsyncRequest, $Request);

        $this->app->unregister('request');
        $this->app->register('request', Request::class, [], function () use (&$Request, $AsyncRequest) { // @phpstan-ignore-line
            if ($this->isMultiPartFormData($Request) || $this->isXWwwFormUrlEncoded($Request)) {
                $Request = $this->handlePostData($AsyncRequest, $Request);
            }

            if ($this->isMultiPartFormData($Request)) {
                $Request = $this->handleUploadedFiles($AsyncRequest, $Request);
            }

            $Request = $this->handleGetData($AsyncRequest, $Request);

            $Request = $this->copyCookies($AsyncRequest, $Request);

            $Request = $this->copyBody($AsyncRequest, $Request);

            return $Request;
        });

        return $Request;
    }

    /**
     * Takes the build request and outputs a async response
     *
     * @param Request                  $Request        - Request
     * @param AsyncResponseInterface $AsyncResponse - AsyncResponseInterface
     *
     * @return AsyncResponseInterface
     */
    protected function buildAsyncResponseFromFlightRequest(Request $Request, AsyncResponseInterface $AsyncResponse): AsyncResponseInterface
    {

        $this->app->unregister('response');
        $this->app->register('response', Response::class);

        $this->app->start();

        $Response = $this->app->response();

        $AsyncResponse->setHttpStatus($Response->status());

        // @TODO set the cookies in here.

        if (count($Response->getHeaders()) > 0) {
            foreach ($Response->getHeaders() as $header_field => $value) {
                $AsyncResponse->setHeader(strtolower($header_field), $value);
            }
        }

        if (strlen($Response->getBody()) > 0) {
            $AsyncResponse->setBody($Response->getBody());
        }

        return $AsyncResponse;
    }

    /**
     * @param AsyncRequestInterface $AsyncRequest - AsyncRequestInterface
     * @param Request                 $Request       - Request
     *
     * @return Request
     */
    protected function copyCookies(AsyncRequestInterface $AsyncRequest, Request $Request): Request
    {
        if (empty($AsyncRequest->getCookies())) {
            return $Request;
        }

        $cookie_strings = [];
        foreach ($AsyncRequest->getCookies() as $name => $value) {
            $_COOKIE[$name]   = $value;
            $cookie_strings[] = $name . '=' . $value;
        }

        $Request->cookies = new Collection($_COOKIE);

        return $Request;
    }

    /**
     * @param AsyncRequestInterface $AsyncRequest - AsyncRequestInterface
     * @param Request                 $Request       - Request
     *
     * @return Request
     */
    protected function copyBody(AsyncRequestInterface $AsyncRequest, Request $Request): Request
    {
        if (empty($AsyncRequest->getBody())) {
            return $Request;
        }

        $Request->body = $AsyncRequest->getBody();
        return $Request;
    }

    // /**
    //  * @param AsyncRequestInterface $AsyncRequest - AsyncRequestInterface
    //  * @param Request                 $Request       - Request
    //  *
    //  * @return Request
    //  */
    // protected function copyHeaders(AsyncRequestInterface $AsyncRequest, Request $Request): Request {

    //  foreach ($AsyncRequest->getHeaders() as $key => $val) {
    //      $Request->setHeader($key, $val);
    //  }

    //  return $Request;
    // }

    /**
     * @param Request $Request - Request
     *
     * @return boolean
     */
    protected function isMultiPartFormData(Request $Request): bool
    {

        if (
            $Request->getHeader('content-type', '') === ''
            || stripos($Request->getHeader('content-type'), 'multipart/form-data') === false
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param Request $Request - Request
     *
     * @return boolean
     */
    protected function isXWwwFormUrlEncoded(Request $Request): bool { // phpcs:ignore

        if (
            $Request->getHeader('content-type', '') === ''
            || stripos($Request->getHeader('content-type'), 'application/x-www-form-urlencoded') === false
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param AsyncRequestInterface $AsyncRequest - AsyncRequestInterface
     * @param Request                 $Request       - Request
     *
     * @return Request
     */
    protected function handleUploadedFiles(AsyncRequestInterface $AsyncRequest, Request $Request): Request
    {
        if (empty($AsyncRequest->getFiles())) {
            return $Request;
        }

        $_FILES = $AsyncRequest->getFiles();

        return $Request;
    }

    /**
     * @param AsyncRequestInterface $AsyncRequest - AsyncRequest
     * @param Request                 $Request       - Request
     *
     * @return Request
     */
    protected function handlePostData(AsyncRequestInterface $AsyncRequest, Request $Request): Request
    {
        if (empty($AsyncRequest->getPost())) {
            return $Request;
        }

        $_POST = $AsyncRequest->getPost();

        return $Request;
    }

    /**
     * @param AsyncRequestInterface $AsyncRequest - AsyncRequest
     * @param Request                 $Request       - Request
     *
     * @return Request
     */
    protected function handleGetData(AsyncRequestInterface $AsyncRequest, Request $Request): Request
    {
        if (empty($AsyncRequest->getGet())) {
            return $Request;
        }

        $_GET = $AsyncRequest->getGet();

        return $Request;
    }
}
