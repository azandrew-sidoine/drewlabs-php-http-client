<?php

namespace Drewlabs\HttpClient\Core;

use Drewlabs\HttpClient\Contracts\ProxiedHttpClientInterface;

class ProxiedHttpClient extends DrewlabsHttpClient implements ProxiedHttpClientInterface
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
        // $this->resetMiddlewareStack();
        // $this->mapAttributeToInitialValues();
    }

    /**
     * @inheritDoc
     */
    public function request($method, $uri = '', $options = [])
    {
        $options[$this->requestBodyAttribute] = [
            '_body' => $options[$this->requestBodyAttribute] ?? [],
            '_endpoint' => [
                'base_uri' => $this->remote_host,
                'path' => $uri
            ]
        ];
        return parent::request($method, $this->proxy_resource_path ?? '', $options);
    }
}
