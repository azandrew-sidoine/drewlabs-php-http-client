<?php

namespace Drewlabs\HttpClient\Core;

class HttpClientInterceptors
{

    /**
     * Undocumented function
     *
     * @param callable $callback Function that accepts a RequestInterface and
     *                     returns a RequestInterface.
     * @return void
     */
    public static function mapTransformRequest(callable $callback)
    {
        return function (callable $handler) use ($callback) {
            return function (
                \Psr\Http\Message\RequestInterface $request,
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
                \Psr\Http\Message\ResponseInterface $request,
                array $options
            ) use ($handler, $callback) {
                return $handler($request, $options)->then($callback);
            };
        };
    }
}
