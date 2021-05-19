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
            'verify' => false
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
        if ($this->requestContentType) {
            $this->requestOptions = $this->mergeWithRequestOptions(array(
                'headers' => [
                    ClientHelpers::HTTP_CLIENT_CONTENT_TYPE_HEADER => $this->requestContentType
                ]
            ));
        }
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
            $options[$this->requestBodyAttribute] = array_merge($options[$this->requestBodyAttribute], $this->attachedFiles);
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
