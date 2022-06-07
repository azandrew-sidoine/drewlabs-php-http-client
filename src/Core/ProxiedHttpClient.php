<?php

namespace Drewlabs\HttpClient\Core;

use Drewlabs\HttpClient\Contracts\ProxiedHttpClientInterface;
use Drewlabs\HttpClient\Traits\HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/** @package Drewlabs\HttpClient\Core */
final class ProxiedHttpClient implements ProxiedHttpClientInterface
{
    use HttpClient;

    /**
     * Request client instance
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * URL to the remote host HTTP server
     *
     * @var string
     */
    private $remoteHost;

    /**
     * Proxy endpoint path
     *
     * @var string
     */
    private $proxyPath;

    public function __construct(
        ?string $host = null,
        ?string $proxyPath = null,
        ?string $remoteHost = null
    ) {
        $this->proxy($host, $proxyPath, $remoteHost);
        $this->resetMiddlewareStack();
        $this->mapAttributesToDefaults();
    }

    private function createClient(?string $uri = null)
    {
        $this->client = new Client([
            'base_uri' => $uri,
            'verify' => false
        ]);
    }

    public function to(string $host)
    {
        $this->remoteHost = $host;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function proxy(
        $proxyHost,
        $proxyPath = null,
        $remoteHost = null
    ) {
        $this->createClient($proxyHost);
        $this->proxyPath = $proxyPath ?? $this->proxyPath;
        $this->remoteHost = $remoteHost ?? $this->remoteHost;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function request(string $method, string $uri = '', ?array $options = [])
    {
        $options[$this->bodyType] = [
            '__body__' => $options[$this->bodyType] ?? [],
            '__endpoint__' => [
                'base_uri' => $this->remoteHost,
                'path' => $uri
            ],
            '__method__' => $method
        ];
        return $this->handleRequest('POST', $this->proxyPath ?? '', $options);
    }
}
