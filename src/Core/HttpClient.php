<?php

namespace Drewlabs\HttpClient\Core;

use Drewlabs\HttpClient\Contracts\HttpClientInterface;
use Drewlabs\HttpClient\Exceptions\ConnectionException;
use Drewlabs\HttpClient\Traits\HttpClient as HttpClientTrait;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/** @package Drewlabs\HttpClient\Core */
class HttpClient implements HttpClientInterface
{

    use HttpClientTrait;

    /**
     * Request client instance
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     *
     * @var string
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
        'requestBodyAttribute' => BodyType::JSON,
        'requestContentType' => ContentType::JSON,
        'attachedFiles' => [],
    ];

    /**
     *
     * @var HandlerStack
     */
    private $middlewareStack;

    public function __construct(ClientInterface $client = null, $baseURI = null)
    {
        $this->client = $client ?? new Client([
            'base_uri' => $baseURI,
            'verify' => false
        ]);
        $this->resetMiddlewareStack();
        $this->mapAttributeToInitialValues();
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->send($request);
    }

    /**
     * Make an HTTP request with the provided parameters
     *
     * @param string $method
     * @param string $uri
     * @param string $options
     * @return ResponseInterface
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
        return ClientHelpers::retry($retries, function () use ($method, $uri, $options, &$retries) {
            try {
                $this->withContentType();
                $opts = $this->mergeWithRequestOptions($options);
                // Initialize the client property on each request call
                $this->mapAttributeToInitialValues();
                $response = $this->client->request($method, $uri, $opts);
                return $response;
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                if ($retries >= 1) {
                    throw new ConnectionException('Connection error : ' . $e->getMessage() . "\n");
                }
            }
        }, $this->retryDelay ?? 100);
    }
}
