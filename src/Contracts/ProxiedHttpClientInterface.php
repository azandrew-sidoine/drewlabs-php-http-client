<?php

namespace Drewlabs\HttpClient\Contracts;

interface ProxiedHttpClientInterface extends HttpClientInterface
{
    /**
     * Create an http client that send requests through proxy server
     *
     * @param string $proxy_server_url
     * @param string|null $resource_path
     * @param string|null $remote_host
     * @return static
     */
    public function proxy($proxy_server_url, $resource_path = null, $remote_host = null);
}