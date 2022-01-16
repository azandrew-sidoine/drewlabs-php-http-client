<?php

namespace Drewlabs\HttpClient\Core;

use Drewlabs\HttpClient\Contracts\HttpClientInterface;
use GuzzleHttp\ClientInterface;

class HttpClientCreator
{
    /**
     * Create a Simple HTTP Request Client provider
     *
     * @param string $uri
     * @return HttpClientInterface
     */
    public static function createHttpClient(?string $uri = null)
    {
        return (new HttpClient(null, $uri));
    }

    /**
     * 
     * @param ClientInterface $client 
     * @return HttpClient 
     */
    public static function createHttpClientFromGuzzleClient(ClientInterface $client)
    {
        return (new HttpClient($client));
    }

    /**
     * Create an HTTP proxy server client
     *
     * @param string $host
     * @param string|null $path
     * @param string|null $remoteHost
     * 
     * @return HttpClientInterface
     */
    public static function createHttProxyClient(
        string $host,
        ?string $path = null,
        ?string $remoteHost = null
    ) {
        return (new ProxiedHttpClient($host, $path))->to($remoteHost);
    }
}
