<?php

declare(strict_types=1);

require_once __DIR__ . '/../Http/Request.php';
require_once __DIR__ . '/../Http/ResponseFactory.php'; //controller returns standard success/error responses
require_once __DIR__ . '/../src/Cache/CacheService.php'; //controller delegates cache work to service
require_once __DIR__ . '/../Logging/Logger.php';

class CacheController
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
    private function cacheSetRateLimitCheck(Request $request): bool
    {
        // For now no rate limit.
        return true;
    }

    // Placeholder idempotency check
    private function cacheSetIdempotencyCheck(Request $request): bool
    {
        // for now duplicate set is allowed because set overwrites value.
        return true;
    }

    // Placeholder rate-limit check for get
    private function cacheGetRateLimitCheck(Request $request): bool
    {
        // for now no rate limit!
        return true;
    }

    // Placeholder idempotency check for get
    private function cacheGetIdempotencyCheck(Request $request): bool
    {
        // GET is safe for now so duplicate request is okay
        return true;
    }

    // Placeholder rate-limit check for delete
    private function cacheDeleteRateLimitCheck(Request $request): bool
    {
        // For now no rate limit!
        return true;
    }

    // Placeholder idempotency check for delete
    private function cacheDeleteIdempotencyCheck(Request $request): bool
    {
        // delete missing key is also success so duplicate delete is okay
        return true;
    }

    // Audit logger for normal cache api
    private function cacheAuditLog(string $action, array $context): void
    {
        Logger::logInfo('CacheController', $action, $context);
    }

    // Boundary function for POST /v1/cache/set.
    public function set(Request $request): JsonResponse
    {
        // Validate
        if ($request->getMethod() !== 'POST') {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'CACHE-4051', 'POST method required', [], 405);
        }

        // Validate JSON
        if ($request->getHasInvalidJson() === true) {
            $this->cacheAuditLog('cache_set_invalid_json', []);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'CACHE-4001', 'Malformed JSON body', [], 400);
        }

        // get key from request body
        $key = $request->getBodyField('key');

        // get value now
        $value = $request->getBodyField('value');

        // Get ttl now
        $ttl = $request->getBodyField('ttl');

        // Validate key presence and type!!!!
        if (!is_string($key) || $key === '') {

            $this->cacheAuditLog('cache_set_validation_failed', ['reason' => 'key missing or invalid']);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'CACHE-4002', 'key is required and must be a string', ['key' => 'required string'], 400);
        }

        // sanitize key now
        $key = trim($key);

        // validate value existence
        if (!array_key_exists('value', $request->getBody())) {
            $this->cacheAuditLog('cache_set_validation_failed', ['reason' => 'value missing', 'key' => $key]);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'CACHE-4003', 'value is required', ['value' => 'required'], 400);
        }

        // validate ttl if provided
        if ($ttl !== null && !is_int($ttl)) {

            $this->cacheAuditLog('cache_set_validation_failed', ['reason' => 'ttl invalid', 'key' => $key]);
            return $this->_responseFactory->error('Validation failed', 'CACHE-4004', 'ttl must be an integer', ['ttl' => 'integer required'], 400);
        }

        // rate-limit placeholder
        if ($this->cacheSetRateLimitCheck($request) !== true) {

            $this->cacheAuditLog('cache_set_rate_limit_failed', ['key' => $key]);
            return $this->_responseFactory->error('Rate limit exceeded', 'CACHE-4291', 'Too many requests', [], 429);
        }

        // idempotency placeholder
        if ($this->cacheSetIdempotencyCheck($request) !== true) {
            $this->cacheAuditLog('cache_set_idempotency_failed', ['key' => $key]);
            return $this->_responseFactory->error('Duplicate request', 'CACHE-4091', 'Duplicate request detected', [], 409);
        }

        //WE DONT NEED TRY CATCH WITH INTERNAL SERVICES!!!!
        // try {
            // delegation now
            $item = $this->_cacheService->set($key, $value, $ttl);
        // } catch (Exception $exception) {
        //     $this->cacheAuditLog('cache_set_service_failed', ['key' => $key, 'errorMessage' => $exception->getMessage()]);
        //     return $this->_responseFactory->error('Validation failed', 'CACHE-4005', $exception->getMessage(), [], 400);
        // }

        // audit success!!!!!!!
        $this->cacheAuditLog('cache_set_success', ['key' => $key]);
        return $this->_responseFactory->success('Cache item stored', $item->toArray(), 200);
    }

    // Boundary function for GET /v1/cache/get
    public function get(Request $request): JsonResponse
    {
        // Validate method
        if ($request->getMethod() !== 'GET') 
            {
                // Return 405 if wrong method
                return $this->_responseFactory->error('Method not allowed', 'CACHE-4052', 'GET method required', [], 405);
            }

        // Get key from query params
        $key = $request->getQueryParam('key');

        // Validate key
        if (!is_string($key) || $key === '') 
            {
                $this->cacheAuditLog('cache_get_validation_failed', ['reason' => 'key missing or invalid']);
                return $this->_responseFactory->error('Validation failed', 'CACHE-4006', 'key query parameter is required', ['key' => 'required string'], 400);
            }

        // sanitize key now
        $key = trim($key);

        // rate-limit placeholder
        if ($this->cacheGetRateLimitCheck($request) !== true) 
            {
                $this->cacheAuditLog('cache_get_rate_limit_failed', ['key' => $key]);
                return $this->_responseFactory->error('Rate limit exceeded', 'CACHE-4292', 'Too many requests', [], 429);
            }

        // idempotency placeholder
        if ($this->cacheGetIdempotencyCheck($request) !== true) 
            {
                $this->cacheAuditLog('cache_get_idempotency_failed', ['key' => $key]);
                return $this->_responseFactory->error('Duplicate request', 'CACHE-4092', 'Duplicate request detected', [], 409);
            }

        try 
        {
            // delegation now
            $item = $this->_cacheService->get($key);
        } 
        catch (Exception $exception) 
        {
            $this->cacheAuditLog('cache_get_service_failed', ['key' => $key, 'errorMessage' => $exception->getMessage()]);
            return $this->_responseFactory->error('Validation failed', 'CACHE-4007', $exception->getMessage(), [], 400);
        }

        // if missing or expired
        if ($item === null) {
            $this->cacheAuditLog('cache_get_not_found', ['key' => $key]);
            return $this->_responseFactory->error('Cache item not found', 'CACHE-4041', 'Key not found or expired', [], 404);
        }

        // audit success!!!!!!!
        $this->cacheAuditLog('cache_get_success', ['key' => $key]);

        // return found item
        return $this->_responseFactory->success('Cache item retrieved', $item->toArray(), 200);
    }

    // Boundary function for POST or DELETE /v1/cache/delete.
    public function delete(Request $request): JsonResponse
    {
        // Validate method
        if ($request->getMethod() !== 'POST' && $request->getMethod() !== 'DELETE') {
            // Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'CACHE-4053', 'POST or DELETE method required', [], 405);
        }

        // Validate JSON
        if ($request->getHasInvalidJson() === true) {
            $this->cacheAuditLog('cache_delete_invalid_json', []);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'CACHE-4008', 'Malformed JSON body', [], 400);
        }

        // get key from body
        $key = $request->getBodyField('key');

        // Validate key
        if (!is_string($key) || $key === '') {
            $this->cacheAuditLog('cache_delete_validation_failed', ['reason' => 'key missing or invalid']);

            // return 400
            return $this->_responseFactory->error('Validation failed', 'CACHE-4009', 'key is required and must be a string', ['key' => 'required string'], 400);
        }

        // sanitize key now
        $key = trim($key);

        // rate-limit placeholder
        if ($this->cacheDeleteRateLimitCheck($request) !== true) 
            {
                $this->cacheAuditLog('cache_delete_rate_limit_failed', ['key' => $key]);
                return $this->_responseFactory->error('Rate limit exceeded', 'CACHE-4293', 'Too many requests', [], 429);
            }

        // idempotency placeholder
        if ($this->cacheDeleteIdempotencyCheck($request) !== true) 
            {
                $this->cacheAuditLog('cache_delete_idempotency_failed', ['key' => $key]);
                return $this->_responseFactory->error('Duplicate request', 'CACHE-4093', 'Duplicate request detected', [], 409);
            }

        try 
        {
            // delegation now
            $deleteResult = $this->_cacheService->delete($key);
        } 
        
        catch (Exception $exception) 
        {
            $this->cacheAuditLog('cache_delete_service_failed', ['key' => $key, 'errorMessage' => $exception->getMessage()]);
            return $this->_responseFactory->error('Validation failed', 'CACHE-4010', $exception->getMessage(), [], 400);
        }

        // audit success!!!!!!!
        $this->cacheAuditLog('cache_delete_success', ['key' => $key, 'deleted' => $deleteResult['deleted']]);

        // return success even if key was missing
        return $this->_responseFactory->success('Cache delete completed', [
            'key' => $key,
            'value' => $deleteResult['value'],
            'deleted' => $deleteResult['deleted']
        ], 200);
    }
}
