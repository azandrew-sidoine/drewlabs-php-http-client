<?php

namespace Drewlabs\HttpClient\Traits;

use Drewlabs\HttpClient\Core\BodyType;
use Drewlabs\HttpClient\Core\ClientHelpers;
use Drewlabs\HttpClient\Core\ContentType;
use Drewlabs\HttpClient\Core\Options;
use GuzzleHttp\Utils;

trait HttpClient
{
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
        $this->requestBodyAttribute = $value;
        return $this;
    }

    private function withContentType()
    {
        if ($this->requestContentType) {
            $this->requestOptions = $this->mergeWithRequestOptions([
                Options::HEADERS => [
                    ClientHelpers::HTTP_CLIENT_CONTENT_TYPE_HEADER => $this->requestContentType
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

    protected function mapAttributeToInitialValues()
    {
        foreach ($this->attributesWithInitialValues as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    protected function mergeWithRequestOptions(array $options)
    {
        $options = is_array($options) ? $options : [];
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
     * Undocumented function
     *
     * @param string $type
     * @return static
     */
    public function setRequestContentType($type)
    {
        if (isset($type) && !is_string($type)) {
            throw new \RuntimeException('Request content-option must be provided as string');
        }
        $this->requestContentType = $type;
        return $this;
    }

    /**
     * Indicate that redirects should not be followed.
     *
     * @return $this
     */
    public function withoutRedirecting()
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
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
        $this->requestOptions = $this->mergeWithRequestOptions(array(
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
    public function withBasicAuth($username, $secret)
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            Options::AUTHENTICATION => [$username, $secret]
        ));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withDigestAuth($username, $secret)
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            Options::AUTHENTICATION => [$username, $secret, 'digest']
        ));
        return $this;
    }

    public function accept($contentType)
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            Options::ACCEPT => $contentType
        ));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function asMultipart()
    {
        $this->setRequestBodyAttribute(BodyType::MULTIPART);
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
    public function withAttachment($name, $contents, $filename = null, $headers = null)
    {
        $this->asMultipart();
        $this->attachedFiles[] = array_merge(
            [
                'name' => $name,
                'contents' => $contents
            ],
            $filename ? ['filename' => $filename] : [],
            $headers ? ['headers' => $headers ?? []] : []
        );
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withCookies(array $cookies, $domain = '')
    {
        if (!is_string($domain)) {
            throw new \RuntimeException('Provides a fully qualified domain');
        }
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            Options::COOKIE => \GuzzleHttp\Cookie\CookieJar::fromArray($cookies, $domain)
        ));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withTimeout(int $timeout)
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
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
    public function withBearerToken($token, $method = 'Bearer')
    {
        $this->requestOptions = $this->mergeWithRequestOptions([
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
        $this->requestOptions = $this->mergeWithRequestOptions($options);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get($uri = '', array $options = [])
    {
        $this->requestOptions = $this->mergeWithRequestOptions($options);
        return $this->request('GET', $uri, []);
    }

    /**
     * @inheritDoc
     */
    public function post($uri = '', array $data = [], array $options = [])
    {
        if ((BodyType::MULTIPART === $this->requestBodyAttribute) && $this->isAssociativeArray_($data)) {
            $tmp = [];
            foreach ($data as $key => $value) {
                $tmp[] = [
                    'name' => $key,
                    'contents' => $value
                ];
            }
            $data = $tmp;
        }
        return $this->request('POST', $uri, array_merge(
            $options,
            [$this->requestBodyAttribute => $this->parseRequestBody($data)]
        ));
    }

    /**
     * @inheritDoc
     */
    public function patch($uri = '', array $data = [], array $options = [])
    {
        if ((BodyType::MULTIPART === $this->requestBodyAttribute) && $this->isAssociativeArray_($data)) {
            $tmp = [];
            foreach ($data as $key => $value) {
                $tmp[] = [
                    'name' => $key,
                    'contents' => $value
                ];
            }
            $data = $tmp;
        }
        return $this->request('PATCH', $uri, array_merge(
            $options,
            [$this->requestBodyAttribute => $this->parseRequestBody($data)]
        ));
    }

    /**
     * @inheritDoc
     */
    public function put($uri = '', array $data = [], array $options = [])
    {
        if ((BodyType::MULTIPART === $this->requestBodyAttribute) && $this->isAssociativeArray_($data)) {
            $tmp = [];
            foreach ($data as $key => $value) {
                $tmp[] = [
                    'name' => $key,
                    'contents' => $value
                ];
            }
            $data = $tmp;
        }
        return $this->request('PUT', $uri, array_merge(
            $options,
            [$this->requestBodyAttribute => $this->parseRequestBody($data)]
        ));
    }

    /**
     * @inheritDoc
     */
    public function delete($uri = '', array $options = [])
    {
        return $this->request('DELETE', $uri, $options);
    }

    /**
     * @inheritDoc
     */
    public function option($uri = '', array $options = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function head($uri = '', array $options = [])
    {
    }

    /**
     * 
     * @param array|\ArrayAccess $body 
     * @return array 
     */
    private function parseRequestBody($body)
    {
        if ((BodyType::MULTIPART === $this->requestBodyAttribute) &&
            $this->isAssociativeArray_($body)
        ) {
            $tmp = [];
            foreach ($body as $key => $value) {
                $tmp[] = [
                    'name' => $key,
                    'contents' => $value
                ];
            }
            $body = $tmp;
        }
        return $body;
    }

    /**
     * Checks if an array is an associative array.
     *
     * @return bool
     */
    public function isAssociativeArray_(array $value)
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }
}
