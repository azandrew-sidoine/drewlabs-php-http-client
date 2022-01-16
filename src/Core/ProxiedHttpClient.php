<?php

namespace Drewlabs\HttpClient\Core;

use Drewlabs\HttpClient\Contracts\ProxiedHttpClientInterface;

class ProxiedHttpClient extends HttpClient implements ProxiedHttpClientInterface
{
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
    private $proxyApiPath;

    public function __construct(?string $host = null)
    {
        $client = $this->_create_client($host);
        parent::__construct($client);
    }

    private function _create_client($base_uri)
    {
        return $base_uri ? new \GuzzleHttp\Client([
            'base_uri' => $base_uri,
        ]) : null;
    }

    /**
     * @inheritDoc
     */
    public function proxy(
        $proxyHost,
        $proxiApiPath = null,
        $remoteHost = null
    ) {
        $this->proxyApiPath = $proxiApiPath;
        $this->remoteHost = $remoteHost;
        if ($client = $this->_create_client($proxyHost)) {
            $this->client = $client;
        }
    }

    /**
     * @inheritDoc
     */
    public function request($method, $uri = '', $options = [])
    {
        $options[$this->requestBodyAttribute] = [
            '__body__' => $options[$this->requestBodyAttribute] ?? [],
            '__endpoint__' => [
                'base_uri' => $this->remoteHost,
                'path' => $uri
            ],
            '__method__' => $method
        ];
        return parent::request('POST', $this->proxyApiPath ?? '', $options);
    }
}
