<?php

declare(strict_types=1);

require_once __DIR__ . '/CacheItem.php';
require_once __DIR__ . '/../../Logging/Logger.php';

class CacheService
{
    //so we need to save all cache in memory , like such as lets say array of cacheItemsMap
    private array $_cacheItemsMap = []; //array of key-value pairs i.e. item

    private const CACHE_TTL_SECONDS_DEFAULT = 7200;
    private const CACHE_TTL_SECONDS_MAX = 604800;

    private int $_serverStartedAt;


    //now constructor
    public function __construct()
    {
        $this->_serverStartedAt = time();
    }

    //VALIDATION OF KEY,TTL AND VALUE
    public function validateKey(string $key): void
    {
        //not empty, len check and preg check
        if (empty($key) || strlen($key) > 255 || !preg_match('/^[A-Za-z0-9._:-]+$/', $key)) {
            //great now we throw error hehe
            throw new Exception('Invalid key!!');
        }
    }

    private function validateTtl(?int $ttl): int
    {
        //if null then give default hehe
        if ($ttl === null) {
            return self::CACHE_TTL_SECONDS_DEFAULT;
        }

        //not null but absurdly incorrect bad bad
        if ($ttl < 1 || $ttl > self::CACHE_TTL_SECONDS_MAX) {
            throw new Exception('Invalid TTL!!');
        }

        return $ttl;
    }

    private function validateValue(mixed $value): void
    {
        //is it str and len check ofc
        if (is_string($value) && strlen($value) > 1024) {
            throw new Exception('Value too large!!');
        }
    }


    //NOW CORE FUNCTIONS LIKE
    //SET
    //GET
    //DELETE

    public function set(string $key, mixed $value, ?int $ttl = null): CacheItem
    {
        // $this->validateKey($key);
        // $this->validateValue($value);

        // $ttl = $this->validateTtl($ttl);

        //what if already set now
        if (isset($this->_cacheItemsMap[$key])) {
            //updating all the values
            $item = $this->_cacheItemsMap[$key];
            $item->refresh($value, $ttl);
        } else {
            ///now we need to create new item since no found
            $item = new CacheItem($key, $value, $ttl);
        }

        //add this now in the _cacheItemsMap array we have
        $this->_cacheItemsMap[$key] = $item;

        //now log it hehe
        Logger::logInfo('CacheService', 'Item stored', ['key' => $key]);

        return $item;
    }



    public function get(string $key): ?CacheItem
    {
        // $this->validateKey($key);

        //what if is nowhere now
        if (!isset($this->_cacheItemsMap[$key])) {
            return null;
        }

        $item = $this->_cacheItemsMap[$key];

        //now to check what if already expired 
        //very bad

        if ($item->isExpired() === true) {
            unset($this->_cacheItemsMap[$key]);
            return null;
        }


        return $item;
    }


    public function delete(string $key): array
    {
        // $this->validateKey($key);
        if (isset($this->_cacheItemsMap[$key])) {
            $value = $this->_cacheItemsMap[$key]->getValue();
            unset($this->_cacheItemsMap[$key]);

            Logger::logInfo('CacheService', 'Item deleted', ['key' => $key]);

            return ['deleted' => true, 'value' => $value];
        }

        return ['deleted' => false, 'value' => null];
    }


    //NOW ADMIN FUNCTIONS WE HAVE TO ADD
    //purge-all
    //list
    //uptime
    //size
    //health

    public function purgeAll(): int
    {
        $totalItemCount = count($this->_cacheItemsMap);
        $this->_cacheItemsMap = [];
        return $totalItemCount;
    }

    public function list(int $limit = 50): array
    {
        //FIRST CLEANUP BEFORE GIVING
        $this->cleanupExpired();

        $result = [];
        $count = 0;

        foreach ($this->_cacheItemsMap as $item) {
            if ($count >= $limit) break;

            $result[] = $item->toArray();
            $count++;
        }

        return $result;
    }

    public function size(): array
    {
        $this->cleanupExpired();

        return [
            'itemCount' => count($this->_cacheItemsMap),
            'processMemoryBytes' => memory_get_usage(true) //returns how much memory PHP has allocated from the system for the current process //if it is false-Actual memory used by our php vars, if true-actually allocated by os(can be bigger)
        ];
    }

    public function uptime(): array
    {
        return [
            'startedAt' => gmdate('c', $this->_serverStartedAt),
            'uptimeSeconds' => time() - $this->_serverStartedAt
        ];
    }

    public function health(): array
    {
        $this->cleanupExpired();

        return [
            'status' => 'ok',
            'itemCount' => count($this->_cacheItemsMap),
            'uptimeSeconds' => time() - $this->_serverStartedAt
        ];
    }

    // CLEANUP OF EXPIRED _cacheItemsMap
    private function cleanupExpired(): void
    {
        foreach ($this->_cacheItemsMap as $key => $item) 
        {
            if ($item->isExpired()) 
            {
                unset($this->_cacheItemsMap[$key]);
            }
        }
    }
}
