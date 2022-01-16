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
    public static function createHttpClient(?string $base_uri = null)
    {
        return (new HttpClient(null, $base_uri));
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
     * @param string $proxy_server_url
     * @param string|null $proxy_resource_path
     * @param string|null $remote_host
     * @return HttpClientInterface
     */
    public static function createHttProxyClient(
        string $proxy_server_url,
        ?string $proxy_resource_path = null,
        ?string $remote_host = null
    ) {
        return (new ProxiedHttpClient)->proxy($proxy_server_url, $proxy_resource_path, $remote_host);
    }
}
