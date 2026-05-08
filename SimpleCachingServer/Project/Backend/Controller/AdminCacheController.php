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
        // Validate method
        if ($request->getMethod() !== 'POST') {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4051', 'POST method required', [], 405);
        }

        // Validate JSON
        if ($request->getHasInvalidJson() === true) {
            $this->adminCacheAuditLog('admin_cache_bulk_set_invalid_json', []);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'ADMIN-CACHE-4001', 'Malformed JSON body', [], 400);
        }

        // get items from request body
        $items = $request->getBodyField('items');

        // Validate items array!!!!
        if (!is_array($items)) {

            $this->adminCacheAuditLog('admin_cache_bulk_set_validation_failed', ['reason' => 'items missing or invalid']);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'ADMIN-CACHE-4002', 'items must be an array', ['items' => 'array required'], 400);
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
            //item must be arr/obj
            if (!is_array($item)) {
                $skipped++;
                $itemErrors[] = ['index' => $index, 'message' => 'Item must be an object'];
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

            //Validate key presence and type!!!!
            if (!is_string($key) || $key === '') {

                $skipped++;
                $itemErrors[] = ['index' => $index, 'message' => 'key is required and must be string'];
                continue;
            }

            // sanitize key now
            $key = trim($key);

            // validate value existence
            if ($hasValue !== true) {
                $skipped++;
                $itemErrors[] = ['index' => $index, 'key' => $key, 'message' => 'value is required'];
                continue;
            }

            // validate ttl if provided
            if ($ttl !== null && !is_int($ttl)) {

                $skipped++;
                $itemErrors[] = ['index' => $index, 'key' => $key, 'message' => 'ttl must be integer'];
                continue;
            }

            try 
            {
                //delegation now
                $storedItem = $this->_cacheService->set($key, $value, $ttl);

                //increasing stored count
                $stored++;

                //adding stored item in final list
                $itemResults[] = $storedItem->toArray();
            } 
            
            catch (Exception $exception) 
            {
                //skip item if service rejects
                $skipped++;

                //add item level error
                $itemErrors[] = ['index' => $index, 'key' => $key, 'message' => $exception->getMessage()];
            }
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
        // Validate method
        if ($request->getMethod() !== 'POST') {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4052', 'POST method required', [], 405);
        }

        // Validate JSON
        if ($request->getHasInvalidJson() === true) {
            $this->adminCacheAuditLog('admin_cache_purge_selected_invalid_json', []);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'ADMIN-CACHE-4003', 'Malformed JSON body', [], 400);
        }

        // get keys from request body
        $keys = $request->getBodyField('keys');

        // Validate keys array!!!!
        if (!is_array($keys)) {

            $this->adminCacheAuditLog('admin_cache_purge_selected_validation_failed', ['reason' => 'keys missing or invalid']);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'ADMIN-CACHE-4004', 'keys must be an array', ['keys' => 'array required'], 400);
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
            // key must be string
            if (!is_string($key) || $key === '') {
                $errors[] = ['index' => $index, 'message' => 'key must be non-empty string'];
                continue;
            }

            // sanitize key now
            $key = trim($key);

            try {
                // delegation now
                $deleteResult = $this->_cacheService->delete($key);

                // check if actually removed
                if ($deleteResult['deleted'] === true) 
                {
                    $removed++;
                } 
                else 
                {
                    $notFound++;
                }
            } 
            
            catch (Exception $exception) 
            {
                // add error if service rejects key
                $errors[] = ['index' => $index, 'key' => $key, 'message' => $exception->getMessage()];
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
        // Validate method
        if ($request->getMethod() !== 'POST') {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4053', 'POST method required', [], 405);
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

        try {
            // delegation now
            $removed = $this->_cacheService->purgeAll();
        } catch (Exception $exception) {
            $this->adminCacheAuditLog('admin_cache_purge_all_service_failed', [
                'errorMessage' => $exception->getMessage()
            ]);

            return $this->_responseFactory->error(
                'Internal error',
                'ADMIN-CACHE-5001',
                $exception->getMessage(),
                [],
                500
            );
        }

        // audit success!!!!!!!
        $this->adminCacheAuditLog('admin_cache_purge_all_completed', ['removed' => $removed]);

        // return final result
        return $this->_responseFactory->success('All cache items purged', ['removed' => $removed], 200);
    }

    // Boundary function for GET /v1/admin/cache/list
    public function list(Request $request): JsonResponse
    {
        // Validate method
        if ($request->getMethod() !== 'GET') {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4054', 'GET method required', [], 405);
        }

        // get limit from query params
        $limitRaw = $request->getQueryParam('limit');

        // default limit
        $limit = 50;

        // validate limit only if provided
        if ($limitRaw !== null) {
            // limit should be numeric
            if (!is_numeric($limitRaw)) {
                return $this->_responseFactory->error('Validation failed', 'ADMIN-CACHE-4005', 'limit must be numeric', ['limit' => 'numeric required'], 400);
            }

            // convert limit to int
            $limit = (int)$limitRaw;

            // limit should be in allowed range
            if ($limit < 1 || $limit > 1000) {
                return $this->_responseFactory->error('Validation failed', 'ADMIN-CACHE-4006', 'limit must be between 1 and 1000', ['limit' => '1 to 1000'], 400);
            }
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

        try {
            // delegation now
            $items = $this->_cacheService->list($limit);
        } catch (Exception $exception) {
            $this->adminCacheAuditLog('admin_cache_list_service_failed', [
                'errorMessage' => $exception->getMessage()
            ]);

            return $this->_responseFactory->error(
                'Internal error',
                'ADMIN-CACHE-5002',
                $exception->getMessage(),
                [],
                500
            );
        }

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
        // Validate method
        if ($request->getMethod() !== 'GET') {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4055', 'GET method required', [], 405);
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

        try {
            // delegation now
            $data = $this->_cacheService->uptime();
        } catch (Exception $exception) {
            $this->adminCacheAuditLog('admin_cache_uptime_service_failed', [
                'errorMessage' => $exception->getMessage()
            ]);

            return $this->_responseFactory->error(
                'Internal error',
                'ADMIN-CACHE-5003',
                $exception->getMessage(),
                [],
                500
            );
        }

        // audit success!!!!!!!
        $this->adminCacheAuditLog('admin_cache_uptime_success', []);

        // return final result
        return $this->_responseFactory->success('Cache server uptime fetched', $data, 200);
    }

    // Boundary function for GET /v1/admin/cache/size
    public function size(Request $request): JsonResponse
    {
        // Validate method
        if ($request->getMethod() !== 'GET') {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4056', 'GET method required', [], 405);
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

        try {
            // delegation now
            $data = $this->_cacheService->size();
        } catch (Exception $exception) {
            $this->adminCacheAuditLog('admin_cache_size_service_failed', [
                'errorMessage' => $exception->getMessage()
            ]);

            return $this->_responseFactory->error(
                'Internal error',
                'ADMIN-CACHE-5004',
                $exception->getMessage(),
                [],
                500
            );
        }

        // audit success!!!!!!!
        $this->adminCacheAuditLog('admin_cache_size_success', []);

        // return final result
        return $this->_responseFactory->success('Cache size fetched', $data, 200);
    }

    // Boundary function for GET /v1/admin/cache/health
    public function health(Request $request): JsonResponse
    {
        // Validate method
        if ($request->getMethod() !== 'GET') {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'ADMIN-CACHE-4057', 'GET method required', [], 405);
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

        try {
            // delegation now
            $data = $this->_cacheService->health();
        } catch (Exception $exception) {
            $this->adminCacheAuditLog('admin_cache_health_service_failed', [
                'errorMessage' => $exception->getMessage()
            ]);

            return $this->_responseFactory->error(
                'Internal error',
                'ADMIN-CACHE-5005',
                $exception->getMessage(),
                [],
                500
            );
        }

        // audit success!!!!!!!
        $this->adminCacheAuditLog('admin_cache_health_success', []);

        // return final result
        return $this->_responseFactory->success('Cache server health fetched', $data, 200);
    }
}