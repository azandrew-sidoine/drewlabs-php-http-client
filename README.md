# Drewlabs Http Client library

The Http client library provides methods for making Http request to Http servers using the various HTTP verbs (GET, POST, DELETE, PUT, PATCH).

## Usage

- Http Client

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('https://jsonplaceholder.typicode.com/');

$response = $client->asJson()
    ->withoutRedirecting()
    // ->withTimeout(3)
    ->get('todos/1');
```

- Http Proxy client

```php

use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';
$client = HttpClientCreator::createHttProxyClient('<URL_TO_PROXY_WEBSERVER>', 'proxy', 'https://jsonplaceholder.typicode.com/');

$response = $client->asJson()
    ->withoutRedirecting()
    ->get('todos/1');

$value = json_decode($response->getBody()->getContents());
```
