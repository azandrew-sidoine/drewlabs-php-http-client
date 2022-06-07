<?php

use Drewlabs\HttpClient\Core\HttpClientCreator;
use Drewlabs\HttpClient\Core\Options;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{

    public function testDefaultContentAsJson()
    {
        $client = HttpClientCreator::createHttpClient('http://127.0.0.1:8000');
        $client = $client->withOptions([
            Options::HEADERS => [
                "x-authorization-client-id" => "859782E1-9A2F-49A4-9D42-B59A78E520FB",
                "x-authorization-client-secret" => "wJa60mWPUK2W8AycfziCrWeMWSus4HLAoSV9cq2qb6FTMlmEudoItlbUHwdUw15peIXmF2b2q2LwCYSO0fvvgQ"
            ]
        ]);
        $response = $client->post('auth/v2/login',[
            "username" => "APPSYSADMIN",
            "password" => "homestead"
        ]);
        $contents = json_decode($response->getBody()->getContents());
        $this->assertTrue($contents->authenticated);
    }

    public function testFormRequest()
    {
        $client = HttpClientCreator::createHttpClient('http://127.0.0.1:8000');
        $client = $client->withOptions([
            Options::HEADERS => [
                "x-authorization-client-id" => "859782E1-9A2F-49A4-9D42-B59A78E520FB",
                "x-authorization-client-secret" => "wJa60mWPUK2W8AycfziCrWeMWSus4HLAoSV9cq2qb6FTMlmEudoItlbUHwdUw15peIXmF2b2q2LwCYSO0fvvgQ"
            ]
        ]);
        $response = $client->asFormRequest()->post('auth/v2/login',[
            "username" => "APPSYSADMIN",
            "password" => "homestead"
        ]);
        $contents = json_decode($response->getBody()->getContents());
        $this->assertTrue($contents->authenticated);
    }

    public function testMultipartRequest()
    {
        $client = HttpClientCreator::createHttpClient('http://127.0.0.1:8000');
        $client = $client->withOptions([
            Options::HEADERS => [
                "x-authorization-client-id" => "859782E1-9A2F-49A4-9D42-B59A78E520FB",
                "x-authorization-client-secret" => "wJa60mWPUK2W8AycfziCrWeMWSus4HLAoSV9cq2qb6FTMlmEudoItlbUHwdUw15peIXmF2b2q2LwCYSO0fvvgQ"
            ]
        ]);
        $response = $client->asMultipart()->post('auth/v2/login',[
            "username" => "APPSYSADMIN",
            "password" => "homestead"
        ]);
        $contents = json_decode($response->getBody()->getContents());
        $this->assertTrue($contents->authenticated);
    }

    public function testAddHeader()
    {
        $client = HttpClientCreator::createHttpClient('http://127.0.0.1:8000');
        $client = $client->addHeader('x-authorization-client-id', '859782E1-9A2F-49A4-9D42-B59A78E520FB');
        $this->assertEquals('859782E1-9A2F-49A4-9D42-B59A78E520FB', $client->getHeader('x-authorization-client-id'));
        $this->assertNull($client->getHeader('x-client-lat'));
    }
}