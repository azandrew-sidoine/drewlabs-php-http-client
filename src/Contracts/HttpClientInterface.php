<?php

namespace Drewlabs\HttpClient\Contracts;

use Psr\Http\Client\ClientInterface;

interface HttpClientInterface extends ClientInterface
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
     * 
     * @param string $name 
     * @param mixed $value 
     * @return self 
     */
    public function addHeader(string $name, $value);

    /**
     * 
     * @param string $name 
     * @return mixed 
     */
    public function getHeader(string $name);

    /**
     * This method set request content-type header to  multipart
     */
    public function asMultipart();

    /**
     * Add HTTP basic auth headers to the request options
     *
     * @param string $username
     * @param string $secret
     * @return static
     */
    public function withBasicAuth(string $username, string $secret);

    /**
     * This method accepts the name of the file and its contents. Optionally.
     * It set a multi-part http request header option.
     *
     * @param string|MultipartRequestParamInterface $name
     * @param string|ressource $contents
     * @param string $filename
     * @param array $headers
     * @return static
     */
    public function withAttachment($name, $contents = null, string $filename = null, ?array $headers = null);

    /**
     * Add request cookies to the request before making any request
     *
     * @param array $cookies
     * @param string $domain
     * @return static
     */
    public function withCookies(array $cookies, string $domain = '');

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
    public function withBearerToken(string $token, string $method = 'Bearer');

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
    public function request(string $method, string $uri = '', ?array $options = []);

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
    public function get(string $uri = '', ?array $options = []);

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
    public function post(string $uri = '', ?array $data = [], ?array $options = []);

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
    public function patch(string $uri = '', ?array $data = [], ?array $options = []);

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
    public function put(string $uri = '', ?array $data = [], ?array $options = []);

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
    public function delete(string $uri = '', ?array $options = []);

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
    public function option(string $uri = '', ?array $options = []);

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
    public function head(string $uri = '', ?array $options = []);

    /**
     * Set the type of content the request accept
     * 
     * @param string $type 
     * @return mixed 
     */
    public function accept(string $type);
}
