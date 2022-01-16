<?php

namespace Drewlabs\HttpClient\Contracts;

interface ProxiedHttpClientInterface extends HttpClientInterface
{
    /**
     * Create an http client that send requests through proxy server
     *
     * @param string $proxyHost
     * @param string|null $resource_path
     * @param string|null $remoteHost
     * @return static
     */
    public function proxy(string $proxyHost, ?string $resource_path = null, ?string $remoteHost = null);
}