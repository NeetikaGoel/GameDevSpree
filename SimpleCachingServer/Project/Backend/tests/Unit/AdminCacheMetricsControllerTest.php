<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AdminCacheMetricsControllerTest extends TestCase
{
    //now what we do first
    //creating new admin controller for every test
    private function createController(?CacheService $cacheService = null): AdminCacheMetricsController
    {
        //using given cache service or create new one
        $cacheService = $cacheService ?? new CacheService();
        //creating response factory
        $responseFactory = new ResponseFactory();
        //returning admin controller with dependencies
        return new AdminCacheMetricsController($cacheService, $responseFactory);
    }

    //same request obj too like cachecontroller hehe
    private function createRequest(string $method, array $queryParams = [], array $body = [], bool $hasInvalidJson = false): Request
    {
        return new Request($method, '/', [], $queryParams, $body, json_encode($body) ?: '', $hasInvalidJson);
    }


    //uptime time!!!!!!!
    //so now checking uptime returns uptime data or not
    public function testUptimeReturnsData(): void
    {
        //always creating controller first
        $controller = $this->createController();
        //creating request
        $request = $this->createRequest('GET');
        //calling uptime func
        $response = $controller->uptime($request);
        //assertions now
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('startedAt', $response->getBody()['data']);
        $this->assertArrayHasKey('uptimeSeconds', $response->getBody()['data']);
    }

    //now checking size returns item count and memory which it should!!!!!!!
    public function testSizeReturnsData(): void
    {
        //always creating controller first
        $controller = $this->createController();
        //creating request
        $request = $this->createRequest('GET');
        //calling size func
        $response = $controller->size($request);
        //assertion time!!!
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('itemCount', $response->getBody()['data']);
        $this->assertArrayHasKey('processMemoryBytes', $response->getBody()['data']);
    }

    //checking whether health returns ok status or not, if not- bad!!!
    public function testHealthReturnsOkStatus(): void
    {
        //always creating controller first
        $controller = $this->createController();
        //creating request
        $request = $this->createRequest('GET');
        //call health func finallyyyy
        $response = $controller->health($request);
        //asserting health hehehehehhe
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getBody()['data']['status']);
    }
}
