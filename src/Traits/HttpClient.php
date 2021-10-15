<?php

namespace Drewlabs\HttpClient\Traits;

use Drewlabs\HttpClient\Core\ClientHelpers;

trait HttpClient
{


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
            'allow_redirects' => false
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
            'verify' => false
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
        $this->setRequestBodyAttribute('form_params')->setRequestContentType('application/x-www-form-urlencoded');
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withBasicAuth($username, $secret)
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'auth' => [$username, $secret]
        ));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withDigestAuth($username, $secret)
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'auth' => [$username, $secret, 'digest']
        ));
        return $this;
    }

    public function accept($contentType)
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'Accept' => $contentType
        ));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function asMultipart()
    {
        $this->setRequestBodyAttribute('multipart');
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function asJson()
    {
        $this->setRequestBodyAttribute('json')->setRequestContentType('application/json');
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAttachment($name, $contents, $filename = null, $headers = null)
    {
        $this->asMultipart();
        $this->attachedFiles[] = [
            'name' => $name,
            'contents' => $contents,
            'filename' => $filename,
            'headers' => $headers
        ];
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
            'cookies' => \GuzzleHttp\Cookie\CookieJar::fromArray($cookies, $domain)
        ));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withTimeout(int $timeout)
    {
        $this->requestOptions = $this->mergeWithRequestOptions(array(
            'timeout' => $timeout
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
            'headers' => [
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
        return $this->request('POST', $uri, array_merge(
            $options,
            array($this->requestBodyAttribute => $data)
        ));
    }

    /**
     * @inheritDoc
     */
    public function patch($uri = '', array $data = [], array $options = [])
    {
        return $this->request('PATCH', $uri, array_merge(
            $options,
            array($this->requestBodyAttribute => $data)
        ));
    }

    /**
     * @inheritDoc
     */
    public function put($uri = '', array $data = [], array $options = [])
    {
        return $this->request('PUT', $uri, array_merge(
            $options,
            array($this->requestBodyAttribute => $data)
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
}
