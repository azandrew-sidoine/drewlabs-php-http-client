# Drewlabs Http Client library

## Usage

```php
// Create the HTTP client
$httpClient = new \Drewlabs\HttpClient\Core\DrewlabsHttpClient(
    $client = new \GuzzleHttp\Client([
        'base_uri' => 'http://127.0.0.1:8887/api/',
    ])
);

try {
    $response = $httpClient->asJson()
    ->withoutRedirecting()
    ->withTimeout(3)
    ->post('path', [
        'param1' => 'value1',
        'param2' => 'value2',
        'param3' => 'value3',
    ]);
    // Decode the JSON content gotten from the request response
    $value = json_decode($response->getBody()->getContents());
} catch (\Exception $th) {
    var_dump($th->getMessage());
}
```
