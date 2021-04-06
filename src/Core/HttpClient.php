<?php

namespace Drewlabs\HttpClient\Core;

use Drewlabs\HttpClient\Contracts\HttpClientInterface;
use Drewlabs\HttpClient\Traits\HttpClient as HttpClientTrait;
use GuzzleHttp\Utils;

class HttpClient implements HttpClientInterface
{

    use HttpClientTrait;

    /**
     * Request client instance
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     *
     * @var [type]
     */
    protected $requestBodyAttribute;

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
     *
     * @var string
     */
    private $requestContentType;

    /**
     *
     * @var array
     */
    private $attachedFiles = [];


    private $attributesWithInitialValues = [
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

    public function __construct(\GuzzleHttp\ClientInterface $client = null, $baseURI = null)
    {
        $this->client = $client ?? new \GuzzleHttp\Client([
            'base_uri' => $baseURI,
        ]);
        $this->resetMiddlewareStack();
        $this->mapAttributeToInitialValues();
    }

    protected function mapAttributeToInitialValues()
    {
        foreach ($this->attributesWithInitialValues as $key => $value) {
            # code...
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        return $this;
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

    protected function resetMiddlewareStack()
    {
        $this->middlewareStack = new \GuzzleHttp\HandlerStack();
        $this->middlewareStack->setHandler(Utils::chooseHandler());
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
     * @return \Psr\Http\Message\ResponseInterface
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
        $retries = $this->retries ?? 1;
        return \Drewlabs\HttpClient\Core\ClientHelpers::retry($retries, function () use ($method, $uri, $options, &$retries) {
            try {
                $this->withContentType();
                $opts = $this->mergeWithRequestOptions($options);
                // Initialize the client property on each request call
                $this->mapAttributeToInitialValues();
                $response = $this->client->request($method, $uri, $opts);
                return $response;
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                if ($retries >= 1) {
                    throw new \Drewlabs\HttpClient\Exceptions\ConnectionException('Connection error : ' . $e->getMessage() . "\n");
                }
            }
        }, $this->retryDelay ?? 100);
    }
}