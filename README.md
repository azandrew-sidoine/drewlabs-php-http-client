# Drewlabs Http Client library

The Http client library provides methods for making Http request to Http servers using the various HTTP verbs (GET, POST, DELETE, PUT, PATCH).

## Integration

They recommended way to integrate the library in a PHP project is by using composer.

Simply add the following lines to your composer.json:

```json
// composer.json
{
    "require": {
        // ... Other dependencies
        "drewlabs/http-client": "^1.0"
    },

    // ...
    // Adding github repository
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:liksoft/drewlabs-php-http-client.git"
        }
    ]
}
```

## Usage

- Http Client

-- Sending a request with HTTP GET Verb

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');

$response = $client->asJson()
    ->withoutRedirecting()
    // ->withTimeout(3)
    ->get(
        '<REQUEST_PATH>',
        [
            // Request options
        ]
    );
```

-- Sending a request with HTTP POST Verb

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');

$response = $client
    ->post(
        '<REQUEST_PATH>', 
        [
            'param1' => 'value1',
            'param2' => 'value2'
        ],
        [
            // Request options
        ]
    );
```

-- Sending a request with HTTP PUT Verb

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');

$response = $client
    ->put(
        '<REQUEST_PATH>', 
        [
            'param1' => 'value1',
            'param2' => 'value2'
        ],
        [
            // Request options
        ]
    );
```

-- Sending a request with HTTP DELETE Verb

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');

$response = $client
    ->put(
        '<REQUEST_PATH>',
        [
            // Request options
        ]
    );
```

--- Send Request with Json Content Type

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');
$response = $client
    ->asJson()
    ->get(
        '<REQUEST_PATH>',
        [
            // Request options
        ]
    );
```

--- Send a multipart http request

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');
$response = $client
    ->asMultipart();
    // Call request method
```

--- Prevent server certificate verification

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');
$response = $client
    ->withoutVerifying();
    // Call request method
```

--- Disable request redirection

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');
$response = $client
    ->withoutRedirecting();
    // Call request method
```

--- Basic authentication

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');
$response = $client
    ->withBasicAuth(<USERNAME>, <SECRET>);
    // Call request method
```

--- Accepting content type

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');
$response = $client
    ->accept('application/json');
    // Call request method
```

-- Adding Attachment files

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');
$response = $client
    ->withAttachment(
        '<PARAM_NAME>',
        file_get_contents('/path/to/resource'),
        '<FILENAME>',
        []
    );
    // Call request method
```

-- Add timeout for request retry

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');
$response = $client
    ->withTimeout(3);
    // Call request method
```

-- Define a number of type the request should be retry

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');
$response = $client
    ->retry(2);
    // Call request method
```

-- Add request authorization header

Ex: "Authorization Bearer <TOKEN>"

``` php
use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';

// Create the HTTP client
$client = HttpClientCreator::createHttpClient('<HOST>');
$response = $client
    ->withBearerToken($token, $method = 'Bearer');
    // Call request method
```

- Http Proxy client

```php

use Drewlabs\HttpClient\Core\HttpClientCreator;

require __DIR__ . '/vendor/autoload.php';
$client = HttpClientCreator::createHttProxyClient('<URL_TO_PROXY_WEBSERVER>', 'proxy', '<HOST>');

$response = $client->asJson()
    ->withoutRedirecting()
    ->get('todos/1');

$value = json_decode($response->getBody()->getContents());
```
