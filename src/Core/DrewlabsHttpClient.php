<?php

namespace Drewlabs\HttpClient\Core;

use Drewlabs\HttpClient\Contracts\IDrewlabsHttpClient;

class DrewlabsHttpClient implements IDrewlabsHttpClient
{

    /**
     * Request client instance
     *
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     *
     * @var array
     */
    private $requestOptions = [];

    /**
     *
     * @var int
     */
    private $retries;

    /**
     *
     * @var int
     */
    private $retryDelay;

    /**
     * request 
     *
     * @var [type]
     */
    private $requestBodyAttribute;

    /**
     *
     * @var string
     */
    private $requestContentType;

    /**
     *
     * @var array
     */
    private $attachedFiles = [];


    private $attriutesWithInitialValues = [
        'requestOptions' => [],
        'retries' => null,
        'retryDelay' => null,
        'requestBodyAttribute' => null,
        'requestContentType' => null,
        'attachedFiles' => [],
    ];

    /**
     *
     * @var \GuzzleHttp\HandlerStack
     */
    private $middlewareStack;

    public function __construct(\GuzzleHttp\ClientInterface $client = null)
    {
        $this->client = $client;
        $this->resetMiddlewareStack();
        $this->mapAttributeToInitialValues();
    }

    private function mapAttributeToInitialValues()
    {
        foreach ($this->attriutesWithInitialValues as $key => $value) {
            # code...
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    private function mergeWithRequestOptions(array $options)
    {
        $options = is_array($options) ? $options : [];
        $requestOptions = array_merge($this->requestOptions, []);
        // Remove entries that are not of type array and are present in the option entry
        foreach ($options as $key => $value) {
            # code...
            if (!isset($requestOptions[$key])) {
                continue;
            }
            if (!is_array($requestOptions[$key])) {
                unset($requestOptions[$key]);
                continue;
            }
            $keys = array_keys($requestOptions[$key]);
            $isAssoc = array_filter(($keys), 'is_string') === $keys;
            // Unset the key if it is not an associative array
            if (!$isAssoc) {
                unset($requestOptions[$key]);
                continue;
            }
        }
        return \array_merge_recursive($requestOptions, $options);
    }

    /**
     * Undocumented function
     *
     * @param string $value
     * @return static
     */
    private function setRequestBodyAttribute($value)
    {
        if (isset($value) && !is_string($value)) {
            throw new \RuntimeException('Request body attribute must be provided as string');
        }
        $this->requestBodyAttribute = $value;
        return $this;
    }

    private function resetMiddlewareStack()
    {
        $this->middlewareStack = new \GuzzleHttp\HandlerStack();
        $this->middlewareStack->setHandler(\GuzzleHttp\choose_handler());
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $type
     * @return static
     */
    public function setRequestContentType($type)
    {
        if (isset($type) && !is_string($type)) {
            throw new \RuntimeException('Request content-option must be provided as string');
        }
        $this->requestContentType = $type;
        return $this;
    }

    /**
     * Indicate that redirects should not be followed.
     *
     * @return $this
     */
    public function withoutRedirecting()
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'allow_redirects' => false
        ));
        return $this;
    }

    /**
     * Indicate that TLS certificates should not be verified.
     *
     * @return $this
     */
    public function withoutVerifying()
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'verify' => false
        ));
        return $this;
    }


    /**
     * This method set request content-type header to  application/x-www-form-urlencoded
     *
     * @return static
     */
    public function asFormRequest()
    {
        $this->setRequestBodyAttribute('form_params')->setRequestContentType('application/x-www-form-urlencoded');
        return $this;
    }

    /**
     * Add HTTP basic auth headers to the request options
     *
     * @param string $username
     * @param string $secret
     * @return static
     */
    public function withBasicAuth($username, $secret)
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'auth' => [$username, $secret]
        ));
        return $this;
    }

    /**
     * Add HTTP digests auth headers to the request options
     *
     * @param string $username
     * @param string $secret
     * @return static
     */
    public function withDigestAuth($username, $secret)
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'auth' => [$username, $secret, 'digest']
        ));
        return $this;
    }

    public function accept($contentType)
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'Accept' => $contentType
        ));
        return $this;
    }

    /**
     * Indicate the request is a multi-part form request.
     *
     * @return $this
     */
    public function asMultipart()
    {
        $this->setRequestBodyAttribute('multipart');
        return $this;
    }

    /**
     * Indicates the request content type is json
     *
     * @return static
     */
    public function asJson()
    {
        $this->setRequestBodyAttribute('json')->setRequestContentType('application/json');
        return $this;
    }

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
    public function withAttachment($name, $contents, $filename, $headers = null)
    {
        $this->asMultipart();
        $this->attachedFiles[] = [
            'name' => $name,
            'contents' => $contents,
            'filename' => $filename,
            'headers' => $headers
        ];
        return $this;
    }

    /**
     * Add request cookies to the request before making any request
     *
     * @param array $cookies
     * @return static
     */
    public function withCookies(array $cookies, $domain = '')
    {
        if (!is_string($domain)) {
            throw new \RuntimeException('Provides a fully qualified domain');
        }
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'cookies' => \GuzzleHttp\Cookie\CookieJar::fromArray($cookies, $domain)
        ));
        return $this;
    }

    /**
     * Set request timeout that will be apply to the request client
     *
     * @param integer $timeout
     * @return static
     */
    public function withTimeout(int $timeout)
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'timeout' => $timeout
        ));
        return $this;
    }

    /**
     * Set the number of time the request will be retries along with the timeout
     * interval
     *
     * @param int $retries
     * @param int $timeout
     * @return static
     */
    public function retry(int $retries, int $timeout)
    {
        $this->retries = $retries;
        $this->retryDelay = $timeout;
        return $this;
    }

    /**
     * Add bearer token authorization option to the request header options
     *
     * @param string $token
     * @return static
     */
    public function withBearerToken($token, $method = 'Bearer')
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'headers' => [
                'Authorization' => \trim($method . ' ' . $token)
            ]
        ));
        return $this;
    }

    /**
     * This method helps in specifying additionnal Guzzle http request 
     * options that will be binded with the request client
     *
     * @param array $options
     * @return static
     */
    public function withOptions(array $options)
    {
        $this->requestOptions = $this->mergeWithRequestOptions($options);
        return $this;
    }

    private function withContentType()
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'headers' => [
                'Content-Type' => $this->requestContentType
            ]
        ));
        return $this;
    }

    /**
     * Make an HTTP request with the provided parameters
     *
     * @param string $method
     * @param string $uri
     * @param string $options
     * @return Psr\Http\Message\ResponseInterface
     */
    public function request($method, $uri = '', $options = [])
    {
        if (isset($options[$this->requestBodyAttribute])) {
            $options[$this->requestBodyAttribute] = array_merge(
                // Transform request mapping attributes
                $options[$this->requestBodyAttribute],
                $this->attachedFiles
            );
        }
        return \Drewlabs\HttpClient\Core\ClientHelpers::retry($this->retries ?? 1, function () use ($method, $uri, $options) {
            try {
                $this->withContentType();
                $opts = $this->mergeWithRequestOptions($options);
                // Initialize the client property on each request call
                $this->mapAttributeToInitialValues();
                var_dump($opts);
                die();
                $response = $this->client->request($method, $uri, $opts);
                return $response;
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                if ($this->retries > 1) {
                    throw new \Drewlabs\HttpClient\Exceptions\ConnectionException('Connection error : ' . $e->getMessage() . "\n");
                }
            }
        }, $this->retryDelay ?? 100);
    }

    /**
     * Make a request to the HTTP server with the GET method
     *
     * @param string $uri
     * @param array $options
     * 
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 
     * @return \GuzzleHttp\Promise\PromiseInterface|Psr\Http\Message\ResponseInterface
     */
    public function get($uri = '', array $options = [])
    {
        $this->requestOptions = $this->mergeWithRequestOptions($options);
        return $this->request('GET', $uri, []);
    }

    /**
     * Make a request to the HTTP server with the POST method
     *
     * @param string $uri
     * @param array $data
     * @param array $options
     * 
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 
     * @return \GuzzleHttp\Promise\PromiseInterface|Psr\Http\Message\ResponseInterface
     */
    public function post($uri = '', array $data = [], array $options = [])
    {
        return $this->request('POST', $uri, array_merge(
            $options,
            array($this->requestBodyAttribute => $data)
        ));
    }

    /**
     * Make a request to the HTTP server with the PATCH method
     *
     * @param string $uri
     * @param array $data
     * @param array $options
     * 
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 
     * @return \GuzzleHttp\Promise\PromiseInterface|Psr\Http\Message\ResponseInterface
     */
    public function patch($uri = '', array $data = [], array $options = [])
    {
        return $this->request('PATCH', $uri, array_merge(
            $options,
            array($this->requestBodyAttribute => $data)
        ));
    }

    /**
     * Make a request to the HTTP server with the PUT method
     *
     * @param string $uri
     * @param array $data
     * @param array $options
     * 
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 
     * @return \GuzzleHttp\Promise\PromiseInterface|Psr\Http\Message\ResponseInterface
     */
    public function put($uri = '', array $data = [], array $options = [])
    {
        return $this->request('PUT', $uri, array_merge(
            $options,
            array($this->requestBodyAttribute => $data)
        ));
    }

    /**
     * Make a request to the HTTP server with the DELETE method
     *
     * @param string $uri
     * @param array $options
     * 
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 
     * @return \GuzzleHttp\Promise\PromiseInterface|Psr\Http\Message\ResponseInterface
     */
    public function delete($uri = '', array $options = [])
    {
        // $this->requestOptions = $this->mergeWithRequestOptions($options);
        return $this->request('DELETE', $uri, $options);
    }

    /**
     * Make a request to the HTTP server with the OPTION method
     *
     * @param string $uri
     * @param array $options
     * 
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 
     * @return \GuzzleHttp\Promise\PromiseInterface|Psr\Http\Message\ResponseInterface
     */
    public function option($uri = '', array $options = [])
    {
    }

    /**
     * Make a request to the HTTP server with the HEAD method
     *
     * @param string $uri
     * @param array $options
     * 
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 
     * @return \GuzzleHttp\Promise\PromiseInterface|Psr\Http\Message\ResponseInterface
     */
    public function head($uri = '', array $options = [])
    {
    }
}
