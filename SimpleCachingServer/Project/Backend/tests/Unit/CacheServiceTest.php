<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CacheServiceTest extends TestCase
{
    //so first checking the set new cache item function first
    public function testSetCreatesNewCacheItem(): void
    {
        //new cache service ofc
        $cacheService = new CacheService();
        //now item which sets new cache item
        $item = $cacheService->set('testKey', 'testValue', 60);
        //now assertions
        $this->assertInstanceOf(CacheItem::class, $item);
        $this->assertSame('testKey', $item->getKey());
        $this->assertSame('testValue', $item->getValue());
        $this->assertSame(60, $item->getTtl());
    }

    //now set if called with same key overwrites value of exisitng hehe
    public function testSetSameKeyOverwritesValue(): void
    {
        //new cache service agina
        $cacheService = new CacheService();
        //set with same value
        $cacheService->set('test.key', 'old', 60);
        $item = $cacheService->set('test.key', 'new', 120);

        $this->assertSame('new', $item->getValue());
        $this->assertSame(120, $item->getTtl());
    }

    public function testGetExistingKeyReturnsCacheItem(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('test.key', 'value', 60);

        $item = $cacheService->get('test.key');

        $this->assertInstanceOf(CacheItem::class, $item);
        $this->assertSame('value', $item->getValue());
    }

    public function testGetMissingKeyReturnsNull(): void
    {
        $cacheService = new CacheService();

        $item = $cacheService->get('missing.key');

        $this->assertNull($item);
    }

    public function testGetExpiredKeyReturnsNull(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('short.key', 'value', 1);

        sleep(1);

        $item = $cacheService->get('short.key');

        $this->assertNull($item);
    }

    public function testDeleteExistingKeyReturnsDeletedTrueAndValue(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('test.key', 'value', 60);

        $result = $cacheService->delete('test.key');

        $this->assertTrue($result['deleted']);
        $this->assertSame('value', $result['value']);
    }

    public function testDeleteMissingKeyReturnsDeletedFalse(): void
    {
        $cacheService = new CacheService();

        $result = $cacheService->delete('missing.key');

        $this->assertFalse($result['deleted']);
        $this->assertNull($result['value']);
    }

    public function testPurgeAllRemovesAllItemsAndReturnsCount(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('key.one', 'one', 60);
        $cacheService->set('key.two', 'two', 60);

        $removed = $cacheService->purgeAll();

        $this->assertSame(2, $removed);
        $this->assertNull($cacheService->get('key.one'));
        $this->assertNull($cacheService->get('key.two'));
    }

    public function testListReturnsLiveItems(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('key.one', 'one', 60);
        $cacheService->set('key.two', 'two', 60);

        $items = $cacheService->list();

        $this->assertCount(2, $items);
    }

    public function testListRespectsLimit(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('key.one', 'one', 60);
        $cacheService->set('key.two', 'two', 60);

        $items = $cacheService->list(1);

        $this->assertCount(1, $items);
    }

    public function testListRemovesExpiredItems(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('short.key', 'value', 1);

        sleep(1);

        $items = $cacheService->list();

        $this->assertCount(0, $items);
    }

    public function testSizeReturnsItemCountAndMemoryBytes(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('test.key', 'value', 60);

        $size = $cacheService->size();

        $this->assertSame(1, $size['itemCount']);
        $this->assertArrayHasKey('processMemoryBytes', $size);
        $this->assertIsInt($size['processMemoryBytes']);
    }

    public function testUptimeReturnsStartedAtAndUptimeSeconds(): void
    {
        $cacheService = new CacheService();

        $uptime = $cacheService->uptime();

        $this->assertArrayHasKey('startedAt', $uptime);
        $this->assertArrayHasKey('uptimeSeconds', $uptime);
        $this->assertIsInt($uptime['uptimeSeconds']);
    }

    public function testHealthReturnsStatusItemCountAndUptimeSeconds(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('test.key', 'value', 60);

        $health = $cacheService->health();

        $this->assertSame('ok', $health['status']);
        $this->assertSame(1, $health['itemCount']);
        $this->assertArrayHasKey('uptimeSeconds', $health);
    }
}
