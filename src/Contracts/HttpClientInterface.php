<?php

namespace Drewlabs\HttpClient\Contracts;

interface HttpClientInterface
{
    /**
     * This method set request content-type header to  application/x-www-form-urlencoded
     *
     * @return static
     */
    public function asFormRequest();

    /**
     * This method set request content-type header to  application/json
     */
    public function asJson();

    /**
     * Add HTTP basic auth headers to the request options
     *
     * @param string $username
     * @param string $secret
     * @return static
     */
    public function withBasicAuth($username, $secret);

    /**
     * This method accepts the name of the file and its contents. Optionally.
     * It set a multi-part http request header option.
     *
     * @param string $name
     * @param string|ressource $contents
     * @param string $filename
     * @param array $headers
     * @return static
     */
    public function withAttachment($name, $contents, $filename, $headers = null);

    /**
     * Add request cookies to the request before making any request
     *
     * @param array $cookies
     * @param string $domain
     * @return static
     */
    public function withCookies(array $cookies, $domain = '');

    /**
     * Set request timeout that will be apply to the request client
     *
     * @param integer $timeout
     * @return static
     */
    public function withTimeout(int $timeout);

    /**
     * Set the number of time the request will be retries along with the timeout
     * interval
     *
     * @param int $retries
     * @param int $timeout
     * @return static
     */
    public function retry(int $retries, int $timeout);

    /**
     * Add bearer token authorization option to the request header options
     *
     * @param string $token
     * @param string $method
     * @return static
     */
    public function withBearerToken($token, $method = 'Bearer');

    /**
     * This method helps in specifying additionnal Guzzle http request
     * options that will be binded with the request client
     *
     * @param array $options
     * @return static
     */
    public function withOptions(array $options);

    /**
     * Make an HTTP request with the provided parameters
     *
     * @param string $method
     * @param string $uri
     * @param string $options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request($method, $uri = '', $options = []);

    /**
     * Make a request to the HTTP server with the GET method
     *
     * @param string $uri
     * @param array $options
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($uri = '', array $options = []);

    /**
     * Make a request to the HTTP server with the POST method
     *
     * @param string $uri
     * @param array $data
     * @param array $options
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function post($uri = '', array $data = [], array $options = []);

    /**
     * Make a request to the HTTP server with the PATCH method
     *
     * @param string $uri
     * @param array $data
     * @param array $options
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function patch($uri = '', array $data = [], array $options = []);

    /**
     * Make a request to the HTTP server with the PUT method
     *
     * @param string $uri
     * @param array $data
     * @param array $options
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function put($uri = '', array $data = [], array $options = []);

    /**
     * Make a request to the HTTP server with the DELETE method
     *
     * @param string $uri
     * @param array $options
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete($uri = '', array $options = []);

    /**
     * Make a request to the HTTP server with the OPTION method
     *
     * @param string $uri
     * @param array $options
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function option($uri = '', array $options = []);

    /**
     * Make a request to the HTTP server with the HEAD method
     *
     * @param string $uri
     * @param array $options
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function head($uri = '', array $options = []);
}
