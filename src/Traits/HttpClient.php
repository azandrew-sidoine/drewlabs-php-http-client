<?php

namespace Drewlabs\HttpClient\Traits;

use ArrayIterator;
use Drewlabs\HttpClient\Contracts\MultipartRequestParamInterface;
use Drewlabs\HttpClient\Core\BodyType;
use Drewlabs\HttpClient\Core\ClientHelpers;
use Drewlabs\HttpClient\Core\ContentType;
use Drewlabs\HttpClient\Core\Options;
use Drewlabs\HttpClient\Exceptions\ConnectionException;
use GuzzleHttp\Utils;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait HttpClient
{

    /**
     *
     * @var string
     */
    protected $bodyType;

    /**
     *
     * @var array
     */
    private $requestOptions = [];

    /**
     *
     * @var int
     */
    private $retries;

    /**
     * Delay in microseconds
     *
     * @var int
     */
    private $retryDelay;

    /**
     *
     * @var string
     */
    private $contentType;

    /**
     *
     * @var array
     */
    private $attachedFiles = [];

    /**
     * 
     * @var (array|null|string)[]
     */
    private $defaults = [
        'requestOptions' => [],
        'retries' => null,
        'retryDelay' => null,
        'bodyType' => BodyType::JSON,
        'contentType' => ContentType::JSON,
        'attachedFiles' => [],
    ];

    /**
     *
     * @var HandlerStack
     */
    private $middlewareStack;

    /**
     * 
     * @param array|\Traversable $body 
     * @return array 
     */
    private function parseRequestBody($body)
    {
        if ((BodyType::MULTIPART === $this->bodyType) && ClientHelpers::isAssociative($body)) {
            return iterator_to_array((function () use ($body) {
                foreach ($body as $key => $value) {
                    yield [
                        'name' => $key,
                        'contents' => $value
                    ];
                }
            })());
        } else {
            return iterator_to_array(is_array($body) ? new ArrayIterator($body) : $body);
        }
    }

    /**
     *
     * @param string $value
     * @return static
     */
    private function setRequestBodyAttribute($value)
    {
        if (isset($value) && !is_string($value)) {
            throw new \RuntimeException('Request body attribute must be provided as string');
        }
        $this->bodyType = $value;
        return $this;
    }

    private function withContentType()
    {
        if ($this->contentType) {
            $this->requestOptions = $this->mergeOptions([
                Options::HEADERS => [
                    ClientHelpers::HTTP_CLIENT_CONTENT_TYPE_HEADER => $this->contentType
                ]
            ]);
        }
        return $this;
    }


    protected function resetMiddlewareStack()
    {
        $this->middlewareStack = new \GuzzleHttp\HandlerStack();
        $this->middlewareStack->setHandler(Utils::chooseHandler());
        return $this;
    }

    private function mapAttributesToDefaults()
    {
        foreach ($this->defaults as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    private function mergeOptions(array $options)
    {
        $options = is_array($options) ? $options : (null === $options ? [] : (array)$options);
        $requestOptions = array_merge($this->requestOptions ?: [], []);
        // Remove entries that are not of type array and are present in the option entry
        foreach ($options as $key => $value) {
            # code...
            if (!isset($requestOptions[$key])) {
                continue;
            }
            if (!is_array($requestOptions[$key])) {
                unset($requestOptions[$key]);
                continue;
            }
            $keys = array_keys($requestOptions[$key]);
            $isAssoc = array_filter(($keys), 'is_string') === $keys;
            // Unset the key if it is not an associative array
            if (!$isAssoc) {
                unset($requestOptions[$key]);
                continue;
            }
        }
        return \array_merge_recursive($requestOptions, $options);
    }

    /**
     * {@inheritDoc}
     */
    private function handleRequest(string $method, string $uri = '', ?array $options = [])
    {
        if (isset($options[$this->bodyType])) {
            $options[$this->bodyType] = array_merge(
                // Transform request mapping attributes
                $options[$this->bodyType],
                $this->attachedFiles
            );
        }
        $retries = $this->retries ?? 1;
        return ClientHelpers::retry($retries, function () use ($method, $uri, $options, &$retries) {
            try {
                $this->withContentType();
                $options_ = $this->mergeOptions($options);
                $response = $this->client->request($method, $uri, $options_);
                // Initialize the client property on each request call
                $this->mapAttributesToDefaults();
                return $response;
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                if ($retries >= 1) {
                    throw new ConnectionException('Connection error : ' . $e->getMessage() . "\n");
                }
            }
        }, $this->retryDelay ?? 100);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->send($request);
    }

    /**
     * Undocumented function
     *
     * @param string $type
     * @return static
     */
    public function setRequestContentType(?string $type = null)
    {
        if ((null !== $type) && !is_string($type)) {
            throw new \RuntimeException('Request content-option must be provided as nullable string');
        }
        $this->contentType = $type;
        return $this;
    }

    /**
     * Indicate that redirects should not be followed.
     *
     * @return $this
     */
    public function withoutRedirecting()
    {
        $this->requestOptions = $this->mergeOptions(array(
            Options::ALLOW_REDIRECT => false
        ));
        return $this;
    }

    /**
     * Indicate that TLS certificates should not be verified.
     *
     * @return $this
     */
    public function withoutVerifying()
    {
        $this->requestOptions = $this->mergeOptions(array(
            Options::VERIFY => false
        ));
        return $this;
    }


    /**
     * This method set request content-type header to  application/x-www-form-urlencoded
     *
     * @return static
     */
    public function asFormRequest()
    {
        $this->setRequestBodyAttribute(BodyType::FORM_DATA)
            ->setRequestContentType(ContentType::URL_ENCODED);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withBasicAuth(string $username, string $secret)
    {
        $this->requestOptions = $this->mergeOptions(array(
            Options::AUTHENTICATION => [$username, $secret]
        ));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withDigestAuth(string $username, string $secret)
    {
        $this->requestOptions = $this->mergeOptions(array(
            Options::AUTHENTICATION => [$username, $secret, 'digest']
        ));
        return $this;
    }

    public function accept(string $contentType)
    {
        $this->requestOptions = $this->mergeOptions(array(
            Options::ACCEPT => $contentType
        ));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function asMultipart()
    {
        $this->setRequestBodyAttribute(BodyType::MULTIPART)
            ->setRequestContentType();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function asJson()
    {
        $this->setRequestBodyAttribute(BodyType::JSON)
            ->setRequestContentType(ContentType::JSON);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAttachment($name, $contents = null, string $filename = null, ?array $headers = null)
    {
        $this->asMultipart();
        if ((func_num_args() === 1) &&
            ($name instanceof MultipartRequestParamInterface) &&
            ($name_ = $name->name()) &&
            ($contents_ = $name->content())
        ) {
            $this->attachedFiles[] = array_merge(
                [
                    'name' => $name_,
                    'contents' => $contents_
                ],
                ($value = $name->filename()) ? ['filename' => $value] : [],
                ($headers_ = $name->headers()) ? ['headers' => $headers_] : [],
            );
        } else if ((null !== $contents) && (null !== $name)) {
            $this->attachedFiles[] = array_merge(
                [
                    'name' => $name,
                    'contents' => $contents
                ],
                $filename ? ['filename' => $filename] : [],
                $headers ? ['headers' => $headers ?? []] : []
            );
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withCookies(array $cookies, string $domain = '')
    {
        if (!is_string($domain)) {
            throw new \RuntimeException('Provides a fully qualified domain');
        }
        $this->requestOptions = $this->mergeOptions(array(
            Options::COOKIE => \GuzzleHttp\Cookie\CookieJar::fromArray($cookies, $domain)
        ));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withTimeout(int $timeout)
    {
        $this->requestOptions = $this->mergeOptions(array(
            Options::TIMEOUT => $timeout
        ));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function retry(int $retries, int $timeout)
    {
        $this->retries = $retries;
        $this->retryDelay = $timeout;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withBearerToken(string $token, string $method = 'Bearer')
    {
        $this->requestOptions = $this->mergeOptions([
            Options::HEADERS => [
                ClientHelpers::HTTP_CLIENT_AUTHORIZATION_HEADER => \trim($method . ' ' . $token)
            ]
        ]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withOptions(array $options)
    {
        $this->requestOptions = $this->mergeOptions($options);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addHeader(string $name, $value)
    {
        $self = $this->withOptions([
            Options::HEADERS => [$name => $value]
        ]);
        return $self;
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name)
    {
        $headers = $this->requestOptions[Options::HEADERS] ?? [];
        return $headers[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function get(string $uri = '', ?array $options = [])
    {
        return $this->handleRequest('GET', $uri, $options ?? []);
    }

    /**
     * @inheritDoc
     */
    public function post(string $uri = '', ?array $data = [], ?array $options = [])
    {
        return $this->handleRequest('POST', $uri, array_merge(
            $options ?? [],
            [$this->bodyType => $this->parseRequestBody($data ?? [])]
        ));
    }

    /**
     * @inheritDoc
     */
    public function patch(string $uri = '', ?array $data = [], ?array $options = [])
    {
        return $this->handleRequest('PATCH', $uri, array_merge(
            $options ?? [],
            [$this->bodyType => $this->parseRequestBody($data ?? [])]
        ));
    }

    /**
     * @inheritDoc
     */
    public function put(string $uri = '', ?array $data = [], ?array $options = [])
    {
        return $this->handleRequest('PUT', $uri, array_merge(
            $options ?? [],
            [$this->bodyType => $this->parseRequestBody($data ?? [])]
        ));
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uri = '', ?array $options = [])
    {
        return $this->handleRequest('DELETE', $uri, $options ?? []);
    }

    /**
     * @inheritDoc
     */
    public function option(string $uri = '', ?array $options = [])
    {
        return $this->handleRequest('OPTION', $uri, $options ?? []);
    }

    /**
     * @inheritDoc
     */
    public function head(string $uri = '', ?array $options = [])
    {
        return $this->handleRequest('HEAD', $uri, $options ?? []);
    }
}
