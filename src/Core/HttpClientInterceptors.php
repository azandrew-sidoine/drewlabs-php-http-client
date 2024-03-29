<?php

namespace Drewlabs\HttpClient\Core;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientInterceptors
{

    /**
     *
     * @param callable $callback Function that accepts a RequestInterface and
     *                     returns a RequestInterface.
     * @return void
     */
    public static function mapTransformRequest(callable $callback)
    {
        return function (callable $handler) use ($callback) {
            return function (
                RequestInterface $request,
                array $options
            ) use ($handler, $callback) {

                return $handler($callback($request), $options);
            };
        };
    }

    /**
     * Apply user response transformation callback an instance of psr7 response
     *
     * @param callable $callback Function that accepts a ResponseInterface and
     *                     returns a ResponseInterface.
     * @return void
     */
    public static function mapTransformResponse(callable $callback)
    {
        return function (callable $handler) use ($callback) {
            return function (
                ResponseInterface $request,
                array $options
            ) use ($handler, $callback) {
                return $handler($request, $options)->then($callback);
            };
        };
    }
}
