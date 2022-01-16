<?php

namespace Drewlabs\HttpClient\Contracts;

interface ProxiedHttpClientInterface extends HttpClientInterface
{
    /**
     * Create an http client that send requests through proxy server
     *
     * @param string $proxyHost
     * @param string|null $proxyPath
     * @param string|null $remoteHost
     * @return self
     */
    public function proxy(string $proxyHost, ?string $proxyPath = null, ?string $remoteHost = null);

    /**
     * Set the proxy request remote host
     * 
     * @param string $host
     * 
     * @return self
     */
    public function to(string $host);
}