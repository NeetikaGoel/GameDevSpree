<?php

declare(strict_types=1);

require_once __DIR__ . '/../Http/Request.php';
require_once __DIR__ . '/../Http/ResponseFactory.php'; //controller returns standard success/error responses
require_once __DIR__ . '/../src/Cache/CacheService.php'; //controller delegates cache work to service
require_once __DIR__ . '/../Logging/Logger.php';

class AdminCacheController
{
    //we need cache service obj
    private CacheService $_cacheService;

    // also a response factory obj
    private ResponseFactory $_responseFactory;

    private const CACHE_KEY_LENGTH_MAX = 255;
    private const CACHE_VALUE_STRING_LENGTH_MAX = 1024;
    private const CACHE_TTL_SECONDS_DEFAULT = 7200;
    private const CACHE_TTL_SECONDS_MAX = 604800;
    private const CACHE_LIST_LIMIT_DEFAULT = 50;
    private const CACHE_LIST_LIMIT_MAX = 1000;


    public function __construct(CacheService $_cacheService, ResponseFactory $_responseFactory)
    {
        $this->_cacheService = $_cacheService;
        $this->_responseFactory = $_responseFactory;
    }

    // Placeholder rate-limit check
    private function adminCacheRateLimitCheck(Request $request): bool
    {
        // for now no rate limit!
        return true;
    }

    // Placeholder idempotency check
    private function adminCacheIdempotencyCheck(Request $request): bool
    {
        // for now duplicate admin requests are okay
        return true;
    }

    // Audit logger for admin cache api
    private function adminCacheAuditLog(string $action, array $context): void
    {
        Logger::logInfo('AdminCacheController', $action, $context);
    }

    // Boundary function for POST /v1/admin/cache/bulk-set
    public function bulkSet(Request $request): JsonResponse
    {
        try {
            // Validate method
            if ($request->getMethod() !== 'POST') {
                throw new InvalidArgumentException('POST method required');
            }
        } catch (InvalidArgumentException $exception) {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4051', $exception->getMessage(), [], 405);
        }

        try {
            // Validate JSON
            if ($request->getHasInvalidJson() === true) {
                throw new InvalidArgumentException('Malformed JSON body');
            }
        } catch (InvalidArgumentException $exception) {
            $this->adminCacheAuditLog('admin_cache_bulk_set_invalid_json', []);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'ADMIN-CACHE-4001', $exception->getMessage(), [], 400);
        }

        // get items from request body
        $items = $request->getBodyField('items');

        try {
            // Validate items array!!!!
            if (!is_array($items)) {
                throw new InvalidArgumentException('items must be an array');
            }
        } catch (InvalidArgumentException $exception) {
            $this->adminCacheAuditLog('admin_cache_bulk_set_validation_failed', ['reason' => 'items missing or invalid']);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'ADMIN-CACHE-4002', $exception->getMessage(), ['items' => 'array required'], 400);
        }

        // rate-limit placeholder
        if ($this->adminCacheRateLimitCheck($request) !== true) {

            $this->adminCacheAuditLog('admin_cache_bulk_set_rate_limit_failed', []);
            return $this->_responseFactory->error('Rate limit exceeded', 'ADMIN-CACHE-4291', 'Too many requests', [], 429);
        }

        // idempotency placeholder
        if ($this->adminCacheIdempotencyCheck($request) !== true) {
            $this->adminCacheAuditLog('admin_cache_bulk_set_idempotency_failed', []);
            return $this->_responseFactory->error('Duplicate request', 'ADMIN-CACHE-4091', 'Duplicate request detected', [], 409);
        }

        //total number of items requested
        $requested = count($items);

        //count of items stored successfully
        $stored = 0;

        //count of skipped items hehe
        $skipped = 0;

        //successful item result list
        $itemResults = [];

        //failed item result list
        $itemErrors = [];

        //loop over all submitted items now
        foreach ($items as $index => $item) {
            try {
                //item must be arr/obj
                if (!is_array($item)) {
                    throw new InvalidArgumentException('Item must be an object');
                }
            } catch (InvalidArgumentException $exception) {
                $skipped++;
                $itemErrors[] = ['index' => $index, 'message' => $exception->getMessage()];
                continue;
            }

            //get key from item
            $key = $item['key'] ?? null;

            //check value exists because value itself can be null
            $hasValue = array_key_exists('value', $item);

            //get value now
            $value = $hasValue ? $item['value'] : null;

            //get ttl now
            $ttl = $item['ttl'] ?? null;

            try {
                //Validate key presence and type!!!!
                if (!is_string($key) || $key === '') {
                    throw new InvalidArgumentException('key is required and must be string');
                }
            } catch (InvalidArgumentException $exception) {
                $skipped++;
                $itemErrors[] = ['index' => $index, 'message' => $exception->getMessage()];
                continue;
            }

            try {
                // sanitize key now
                $key = trim($key);

                if ($key === '' || strlen($key) > self::CACHE_KEY_LENGTH_MAX || preg_match('/^[A-Za-z0-9._:-]+$/', $key) !== 1) {
                    throw new InvalidArgumentException('key must be 1 to 255 chars and contain only A-Z a-z 0-9 dot underscore colon hyphen');
                }
            } catch (InvalidArgumentException $exception) {
                $skipped++;
                $itemErrors[] = ['index' => $index, 'key' => is_string($key) ? $key : null, 'message' => $exception->getMessage()];
                continue;
            }

            try {
                // validate value existence
                if ($hasValue !== true) {
                    throw new InvalidArgumentException('value is required');
                }

                if (is_string($value) && strlen($value) > self::CACHE_VALUE_STRING_LENGTH_MAX) {
                    throw new InvalidArgumentException('value string must not exceed 1024 characters');
                }
            } catch (InvalidArgumentException $exception) {
                $skipped++;
                $itemErrors[] = ['index' => $index, 'key' => $key, 'message' => $exception->getMessage()];
                continue;
            }

            try {
                // validate ttl if provided
                if ($ttl !== null && !is_int($ttl)) {
                    throw new InvalidArgumentException('ttl must be integer');
                }

                if ($ttl === null) {
                    $ttl = self::CACHE_TTL_SECONDS_DEFAULT;
                }

                if ($ttl < 1 || $ttl > self::CACHE_TTL_SECONDS_MAX) {
                    throw new InvalidArgumentException('ttl must be between 1 and 604800');
                }
            } catch (InvalidArgumentException $exception) {
                $skipped++;
                $itemErrors[] = ['index' => $index, 'key' => $key, 'message' => $exception->getMessage()];
                continue;
            }

            //delegation now
            $storedItem = $this->_cacheService->set($key, $value, $ttl);

            //increasing stored count
            $stored++;

            //adding stored item in final list
            $itemResults[] = $storedItem->toArray();
        }

        // audit success!!!!!!!
        $this->adminCacheAuditLog('admin_cache_bulk_set_completed', ['requested' => $requested, 'stored' => $stored, 'skipped' => $skipped]);

        // return final result
        return $this->_responseFactory->success('Bulk set completed', [
            'requested' => $requested,
            'stored' => $stored,
            'skipped' => $skipped,
            'items' => $itemResults,
            'errors' => $itemErrors
        ], 200);
    }

    // Boundary function for POST /v1/admin/cache/purge-selected
    public function purgeSelected(Request $request): JsonResponse
    {
        try {
            // Validate method
            if ($request->getMethod() !== 'POST') {
                throw new InvalidArgumentException('POST method required');
            }
        } catch (InvalidArgumentException $exception) {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4052', $exception->getMessage(), [], 405);
        }

        try {
            // Validate JSON
            if ($request->getHasInvalidJson() === true) {
                throw new InvalidArgumentException('Malformed JSON body');
            }
        } catch (InvalidArgumentException $exception) {
            $this->adminCacheAuditLog('admin_cache_purge_selected_invalid_json', []);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'ADMIN-CACHE-4003', $exception->getMessage(), [], 400);
        }

        // get keys from request body
        $keys = $request->getBodyField('keys');

        try {
            // Validate keys array!!!!
            if (!is_array($keys)) {
                throw new InvalidArgumentException('keys must be an array');
            }
        } catch (InvalidArgumentException $exception) {
            $this->adminCacheAuditLog('admin_cache_purge_selected_validation_failed', ['reason' => 'keys missing or invalid']);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'ADMIN-CACHE-4004', $exception->getMessage(), ['keys' => 'array required'], 400);
        }

        // rate-limit placeholder
        if ($this->adminCacheRateLimitCheck($request) !== true) {

            $this->adminCacheAuditLog('admin_cache_purge_selected_rate_limit_failed', []);
            return $this->_responseFactory->error('Rate limit exceeded', 'ADMIN-CACHE-4292', 'Too many requests', [], 429);
        }

        // idempotency placeholder
        if ($this->adminCacheIdempotencyCheck($request) !== true) {
            $this->adminCacheAuditLog('admin_cache_purge_selected_idempotency_failed', []);
            return $this->_responseFactory->error('Duplicate request', 'ADMIN-CACHE-4092', 'Duplicate request detected', [], 409);
        }

        //total requested keys
        $requested = count($keys);

        //count of removed keys
        $removed = 0;

        //count of keys not found
        $notFound = 0;

        //error list
        $errors = [];

        //loop over all keys now
        foreach ($keys as $index => $key) {
            try {
                // key must be string
                if (!is_string($key) || $key === '') {
                    throw new InvalidArgumentException('key must be non-empty string');
                }
            } catch (InvalidArgumentException $exception) {
                $errors[] = ['index' => $index, 'message' => $exception->getMessage()];
                continue;
            }

            try {
                // sanitize key now
                $key = trim($key);

                if ($key === '' || strlen($key) > self::CACHE_KEY_LENGTH_MAX || preg_match('/^[A-Za-z0-9._:-]+$/', $key) !== 1) {
                    throw new InvalidArgumentException('key must be 1 to 255 chars and contain only A-Z a-z 0-9 dot underscore colon hyphen');
                }
            } catch (InvalidArgumentException $exception) {
                $errors[] = ['index' => $index, 'key' => is_string($key) ? $key : null, 'message' => $exception->getMessage()];
                continue;
            }

            // delegation now
            $deleteResult = $this->_cacheService->delete($key);

            // check if actually removed
            if ($deleteResult['deleted'] === true) {
                $removed++;
            } else {
                $notFound++;
            }
        }

        // audit success!!!!!!!
        $this->adminCacheAuditLog('admin_cache_purge_selected_completed', ['requested' => $requested, 'removed' => $removed, 'notFound' => $notFound]);

        // return final result
        return $this->_responseFactory->success('Selected cache keys purged', [
            'requested' => $requested,
            'removed' => $removed,
            'notFound' => $notFound,
            'errors' => $errors
        ], 200);
    }

    // Boundary function for POST /v1/admin/cache/purge-all
    public function purgeAll(Request $request): JsonResponse
    {
        try {
            // Validate method
            if ($request->getMethod() !== 'POST') {
                throw new InvalidArgumentException('POST method required');
            }
        } catch (InvalidArgumentException $exception) {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4053', $exception->getMessage(), [], 405);
        }

        // rate-limit placeholder
        if ($this->adminCacheRateLimitCheck($request) !== true) {

            $this->adminCacheAuditLog('admin_cache_purge_all_rate_limit_failed', []);
            return $this->_responseFactory->error('Rate limit exceeded', 'ADMIN-CACHE-4293', 'Too many requests', [], 429);
        }

        // idempotency placeholder
        if ($this->adminCacheIdempotencyCheck($request) !== true) {
            $this->adminCacheAuditLog('admin_cache_purge_all_idempotency_failed', []);
            return $this->_responseFactory->error('Duplicate request', 'ADMIN-CACHE-4093', 'Duplicate request detected', [], 409);
        }

        // delegation now
        $removed = $this->_cacheService->purgeAll();

        // audit success!!!!!!!
        $this->adminCacheAuditLog('admin_cache_purge_all_completed', ['removed' => $removed]);

        // return final result
        return $this->_responseFactory->success('All cache items purged', ['removed' => $removed], 200);
    }

    // Boundary function for GET /v1/admin/cache/list
    public function list(Request $request): JsonResponse
    {
        try {
            // Validate method
            if ($request->getMethod() !== 'GET') {
                throw new InvalidArgumentException('GET method required');
            }
        } catch (InvalidArgumentException $exception) {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4054', $exception->getMessage(), [], 405);
        }

        // get limit from query params
        $limitRaw = $request->getQueryParam('limit');

        // default limit
        $limit = self::CACHE_LIST_LIMIT_DEFAULT;

        try {
            // validate limit only if provided
            if ($limitRaw !== null) {
                // limit should be numeric
                if (!is_numeric($limitRaw)) {
                    throw new InvalidArgumentException('limit must be numeric');
                }

                // convert limit to int
                $limit = (int)$limitRaw;

                // limit should be in allowed range
                if ($limit < 1 || $limit > self::CACHE_LIST_LIMIT_MAX) {
                    throw new InvalidArgumentException('limit must be between 1 and 1000');
                }
            }
        } catch (InvalidArgumentException $exception) {
            return $this->_responseFactory->error('Validation failed', 'ADMIN-CACHE-4005', $exception->getMessage(), ['limit' => 'numeric 1 to 1000'], 400);
        }

        // rate-limit placeholder
        if ($this->adminCacheRateLimitCheck($request) !== true) {
            $this->adminCacheAuditLog('admin_cache_list_rate_limit_failed', []);
            return $this->_responseFactory->error('Rate limit exceeded', 'ADMIN-CACHE-4294', 'Too many requests', [], 429);
        }

        // idempotency placeholder
        if ($this->adminCacheIdempotencyCheck($request) !== true) {
            $this->adminCacheAuditLog('admin_cache_list_idempotency_failed', []);
            return $this->_responseFactory->error('Duplicate request', 'ADMIN-CACHE-4094', 'Duplicate request detected', [], 409);
        }

        // delegation now
        $items = $this->_cacheService->list($limit);

        // audit success!!!!!!!
        $this->adminCacheAuditLog('admin_cache_list_success', ['limit' => $limit, 'count' => count($items)]);

        // return final result
        return $this->_responseFactory->success('Cache items listed', [
            'count' => count($items),
            'limit' => $limit,
            'items' => $items
        ], 200);
    }

    // Boundary function for GET /v1/admin/cache/uptime
    public function uptime(Request $request): JsonResponse
    {
        try {
            // Validate method
            if ($request->getMethod() !== 'GET') {
                throw new InvalidArgumentException('GET method required');
            }
        } catch (InvalidArgumentException $exception) {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4055', $exception->getMessage(), [], 405);
        }

        // rate-limit placeholder
        if ($this->adminCacheRateLimitCheck($request) !== true) {

            $this->adminCacheAuditLog('admin_cache_uptime_rate_limit_failed', []);
            return $this->_responseFactory->error('Rate limit exceeded', 'ADMIN-CACHE-4295', 'Too many requests', [], 429);
        }

        // idempotency placeholder
        if ($this->adminCacheIdempotencyCheck($request) !== true) {
            $this->adminCacheAuditLog('admin_cache_uptime_idempotency_failed', []);
            return $this->_responseFactory->error('Duplicate request', 'ADMIN-CACHE-4095', 'Duplicate request detected', [], 409);
        }

        // delegation now
        $data = $this->_cacheService->uptime();

        // audit success!!!!!!!
        $this->adminCacheAuditLog('admin_cache_uptime_success', []);

        // return final result
        return $this->_responseFactory->success('Cache server uptime fetched', $data, 200);
    }

    // Boundary function for GET /v1/admin/cache/size
    public function size(Request $request): JsonResponse
    {
        try {
            // Validate method
            if ($request->getMethod() !== 'GET') {
                throw new InvalidArgumentException('GET method required');
            }
        } catch (InvalidArgumentException $exception) {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4056', $exception->getMessage(), [], 405);
        }

        // rate-limit placeholder
        if ($this->adminCacheRateLimitCheck($request) !== true) {

            $this->adminCacheAuditLog('admin_cache_size_rate_limit_failed', []);
            return $this->_responseFactory->error('Rate limit exceeded', 'ADMIN-CACHE-4296', 'Too many requests', [], 429);
        }

        // idempotency placeholder
        if ($this->adminCacheIdempotencyCheck($request) !== true) {
            $this->adminCacheAuditLog('admin_cache_size_idempotency_failed', []);
            return $this->_responseFactory->error('Duplicate request', 'ADMIN-CACHE-4096', 'Duplicate request detected', [], 409);
        }

        // delegation now
        $data = $this->_cacheService->size();

        // audit success!!!!!!!
        $this->adminCacheAuditLog('admin_cache_size_success', []);

        // return final result
        return $this->_responseFactory->success('Cache size fetched', $data, 200);
    }

    // Boundary function for GET /v1/admin/cache/health
    public function health(Request $request): JsonResponse
    {
        try {
            // Validate method
            if ($request->getMethod() !== 'GET') {
                throw new InvalidArgumentException('GET method required');
            }
        } catch (InvalidArgumentException $exception) {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4057', $exception->getMessage(), [], 405);
        }

        // rate-limit placeholder
        if ($this->adminCacheRateLimitCheck($request) !== true) {

            $this->adminCacheAuditLog('admin_cache_health_rate_limit_failed', []);
            return $this->_responseFactory->error('Rate limit exceeded', 'ADMIN-CACHE-4297', 'Too many requests', [], 429);
        }

        // idempotency placeholder
        if ($this->adminCacheIdempotencyCheck($request) !== true) {
            $this->adminCacheAuditLog('admin_cache_health_idempotency_failed', []);
            return $this->_responseFactory->error('Duplicate request', 'ADMIN-CACHE-4097', 'Duplicate request detected', [], 409);
        }

        // delegation now
        $data = $this->_cacheService->health();

        // audit success!!!!!!!
        $this->adminCacheAuditLog('admin_cache_health_success', []);

        // return final result
        return $this->_responseFactory->success('Cache server health fetched', $data, 200);
    }
}
