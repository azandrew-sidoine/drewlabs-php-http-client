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
    private $remote_host;

    /**
     * Proxy endpoint path
     *
     * @var string
     */
    private $proxy_resource_path;

    public function __construct(?string $proxy_host = null)
    {
        $client = $this->_create_client($proxy_host);
        parent::__construct($client);
    }

    private function _create_client($base_uri)
    {
        return isset($base_uri) ? new \GuzzleHttp\Client([
            'base_uri' => $base_uri,
        ]) : null;
    }

    /**
     * @inheritDoc
     */
    public function proxy(
        $proxy_server_url,
        $proxy_resource_path = null,
        $remote_host = null
    ) {
        $this->proxy_resource_path = $proxy_resource_path;
        $this->remote_host = $remote_host;
        $this->client = $this->_create_client($proxy_server_url);
    }

    /**
     * @inheritDoc
     */
    public function request($method, $uri = '', $options = [])
    {
        $options[$this->requestBodyAttribute] = [
            '__body__' => $options[$this->requestBodyAttribute] ?? [],
            '__endpoint__' => [
                'base_uri' => $this->remote_host,
                'path' => $uri
            ],
            '__method__' => $method
        ];
        return parent::request('POST', $this->proxy_resource_path ?? '', $options);
    }
}
