<?php

namespace Drewlabs\HttpClient\Core;

use Drewlabs\HttpClient\Contracts\HttpClientInterface;
use Drewlabs\HttpClient\Traits\HttpClient as HttpClientTrait;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use RuntimeException;

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
     * @param ClientInterface|null $client 
     * @param string $baseURI 
     * @return self 
     * @throws RuntimeException 
     */
    public function __construct(ClientInterface $client = null, $baseURI = null)
    {
        $this->client = $client ?? new Client([
            'base_uri' => $baseURI,
            'verify' => false
        ]);
        $this->resetMiddlewareStack();
        $this->mapAttributesToDefaults();
    }

    /**
     * @inheritDoc
     */
    public function request(string $method, string $uri = '', ?array $options = [])
    {
        return $this->handleRequest($method, $uri, $options);
    }
}
