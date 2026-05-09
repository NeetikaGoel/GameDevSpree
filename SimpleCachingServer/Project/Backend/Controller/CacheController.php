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

    //also a response factory obj
    private ResponseFactory $_responseFactory;

    private const CACHE_KEY_LENGTH_MAX = 255;
    private const CACHE_VALUE_STRING_LENGTH_MAX = 1024;
    private const CACHE_TTL_SECONDS_DEFAULT = 7200;
    private const CACHE_TTL_SECONDS_MAX = 604800;


    public function __construct(CacheService $_cacheService, ResponseFactory $_responseFactory)
    {
        $this->_cacheService = $_cacheService;
        $this->_responseFactory = $_responseFactory;
    }

    //Placeholder rate-limit check
    private function cacheSetRateLimitCheck(Request $request): bool
    {
        //For now no rate limit.
        return true;
    }

    //Placeholder idempotency check
    private function cacheSetIdempotencyCheck(Request $request): bool
    {
        //for now duplicate set is allowed because set overwrites value.
        return true;
    }

    //Placeholder rate-limit check for get
    private function cacheGetRateLimitCheck(Request $request): bool
    {
        //for now no rate limit!
        return true;
    }

    //Placeholder idempotency check for get
    private function cacheGetIdempotencyCheck(Request $request): bool
    {
        //GET is safe for now so duplicate request is okay
        return true;
    }

    //Placeholder rate-limit check for delete
    private function cacheDeleteRateLimitCheck(Request $request): bool
    {
        //For now no rate limit!
        return true;
    }

    //Placeholder idempotency check for delete
    private function cacheDeleteIdempotencyCheck(Request $request): bool
    {
        //delete missing key is also success so duplicate delete is okay
        return true;
    }

    //Audit logger for normal cache api
    private function cacheAuditLog(string $action, array $context): void
    {
        Logger::logInfo('CacheController', $action, $context);
    }

    //Boundary function for POST /v1/cache/set.
    public function set(Request $request): JsonResponse
    {
        try {
            //Validate
            if ($request->getMethod() !== 'POST') {
                throw new InvalidArgumentException('POST method required');
            }
        } catch (InvalidArgumentException $exception) {
            //Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'CACHE-4051', $exception->getMessage(), [], 405);
        }

        try {
            //Validate JSON
            if ($request->getHasInvalidJson() === true) {
                throw new InvalidArgumentException('Malformed JSON body');
            }
        } catch (InvalidArgumentException $exception) {
            $this->cacheAuditLog('cache_set_invalid_json', []);

            //return 400
            return $this->_responseFactory->error('Validation failed', 'CACHE-4001', $exception->getMessage(), [], 400);
        }

        //get key from request body
        $key = $request->getBodyField('key');

        //get value now
        $value = $request->getBodyField('value');

        //Get ttl now
        $ttl = $request->getBodyField('ttl');

        try {
            //Validate key presence and type!!!!
            if (!is_string($key) || $key === '') {
                throw new InvalidArgumentException('key is required and must be a string');
            }
        } catch (InvalidArgumentException $exception) {
            $this->cacheAuditLog('cache_set_validation_failed', ['reason' => 'key missing or invalid']);

            //return 400
            return $this->_responseFactory->error('Validation failed', 'CACHE-4002', $exception->getMessage(), ['key' => 'required string'], 400);
        }

        try {
            //sanitize key now
            $key = trim($key);

            if ($key === '' || strlen($key) > self::CACHE_KEY_LENGTH_MAX || preg_match('/^[A-Za-z0-9._:-]+$/', $key) !== 1) {
                throw new InvalidArgumentException('key must be 1 to 255 chars and contain only A-Z a-z 0-9 dot underscore colon hyphen');
            }
        } catch (InvalidArgumentException $exception) {
            $this->cacheAuditLog('cache_set_validation_failed', ['reason' => 'key format invalid']);

            //return 400
            return $this->_responseFactory->error('Validation failed', 'CACHE-4006', $exception->getMessage(), ['key' => 'invalid format'], 400);
        }

        try {
            //validate value existence
            if (!array_key_exists('value', $request->getBody())) {
                throw new InvalidArgumentException('value is required');
            }

            if (is_string($value) && strlen($value) > self::CACHE_VALUE_STRING_LENGTH_MAX) {
                throw new InvalidArgumentException('value string must not exceed 1024 characters');
            }
        } catch (InvalidArgumentException $exception) {
            $this->cacheAuditLog('cache_set_validation_failed', ['reason' => 'value invalid', 'key' => $key]);

            //return 400
            return $this->_responseFactory->error('Validation failed', 'CACHE-4003', $exception->getMessage(), ['value' => 'required and max 1024 chars if string'], 400);
        }

        try {
            //validate ttl if provided
            if ($ttl !== null && !is_int($ttl)) {
                throw new InvalidArgumentException('ttl must be an integer');
            }

            if ($ttl === null) {
                $ttl = self::CACHE_TTL_SECONDS_DEFAULT;
            }

            if ($ttl < 1 || $ttl > self::CACHE_TTL_SECONDS_MAX) {
                throw new InvalidArgumentException('ttl must be between 1 and 604800');
            }
        } catch (InvalidArgumentException $exception) {
            $this->cacheAuditLog('cache_set_validation_failed', ['reason' => 'ttl invalid', 'key' => $key]);
            return $this->_responseFactory->error('Validation failed', 'CACHE-4004', $exception->getMessage(), ['ttl' => 'integer between 1 and 604800'], 400);
        }

        //rate-limit placeholder
        if ($this->cacheSetRateLimitCheck($request) !== true) {

            $this->cacheAuditLog('cache_set_rate_limit_failed', ['key' => $key]);
            return $this->_responseFactory->error('Rate limit exceeded', 'CACHE-4291', 'Too many requests', [], 429);
        }

        //idempotency placeholder
        if ($this->cacheSetIdempotencyCheck($request) !== true) {
            $this->cacheAuditLog('cache_set_idempotency_failed', ['key' => $key]);
            return $this->_responseFactory->error('Duplicate request', 'CACHE-4091', 'Duplicate request detected', [], 409);
        }

        //WE DONT NEED TRY CATCH WITH INTERNAL SERVICES!!!!
        //delegation now
        $item = $this->_cacheService->set($key, $value, $ttl);

        //audit success!!!!!!!
        $this->cacheAuditLog('cache_set_success', ['key' => $key]);
        return $this->_responseFactory->success('Cache item stored', $item->toArray(), 200);
    }

    //Boundary function for GET /v1/cache/get
    public function get(Request $request): JsonResponse
    {
        try {
            //Validate method
            if ($request->getMethod() !== 'GET') {
                throw new InvalidArgumentException('GET method required');
            }
        } catch (InvalidArgumentException $exception) {
            //Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'CACHE-4052', $exception->getMessage(), [], 405);
        }

        //Get key from query params
        $key = $request->getQueryParam('key');

        try {
            //Validate key
            if (!is_string($key) || $key === '') {
                throw new InvalidArgumentException('key query parameter is required');
            }
        } catch (InvalidArgumentException $exception) {
            $this->cacheAuditLog('cache_get_validation_failed', ['reason' => 'key missing or invalid']);
            return $this->_responseFactory->error('Validation failed', 'CACHE-4006', $exception->getMessage(), ['key' => 'required string'], 400);
        }

        try {
            //sanitize key now
            $key = trim($key);

            if ($key === '' || strlen($key) > self::CACHE_KEY_LENGTH_MAX || preg_match('/^[A-Za-z0-9._:-]+$/', $key) !== 1) {
                throw new InvalidArgumentException('key must be 1 to 255 chars and contain only A-Z a-z 0-9 dot underscore colon hyphen');
            }
        } catch (InvalidArgumentException $exception) {
            $this->cacheAuditLog('cache_get_validation_failed', ['reason' => 'key format invalid']);
            return $this->_responseFactory->error('Validation failed', 'CACHE-4007', $exception->getMessage(), ['key' => 'invalid format'], 400);
        }

        //rate-limit placeholder
        if ($this->cacheGetRateLimitCheck($request) !== true) {
            $this->cacheAuditLog('cache_get_rate_limit_failed', ['key' => $key]);
            return $this->_responseFactory->error('Rate limit exceeded', 'CACHE-4292', 'Too many requests', [], 429);
        }

        //idempotency placeholder
        if ($this->cacheGetIdempotencyCheck($request) !== true) {
            $this->cacheAuditLog('cache_get_idempotency_failed', ['key' => $key]);
            return $this->_responseFactory->error('Duplicate request', 'CACHE-4092', 'Duplicate request detected', [], 409);
        }

        //delegation now
        $item = $this->_cacheService->get($key);

        //if missing or expired
        if ($item === null) {
            $this->cacheAuditLog('cache_get_not_found', ['key' => $key]);
            return $this->_responseFactory->error('Cache item not found', 'CACHE-4041', 'Key not found or expired', [], 404);
        }

        //audit success!!!!!!!
        $this->cacheAuditLog('cache_get_success', ['key' => $key]);

        //return found item
        return $this->_responseFactory->success('Cache item retrieved', $item->toArray(), 200);
    }

    //Boundary function for POST or DELETE /v1/cache/delete.
    public function delete(Request $request): JsonResponse
    {
        try {
            //Validate method
            if ($request->getMethod() !== 'POST' && $request->getMethod() !== 'DELETE') {
                throw new InvalidArgumentException('POST or DELETE method required');
            }
        } catch (InvalidArgumentException $exception) {
            //Return 405 if wrong method
            return $this->_responseFactory->error('Method not allowed', 'CACHE-4053', $exception->getMessage(), [], 405);
        }

        try {
            //Validate JSON
            if ($request->getHasInvalidJson() === true) {
                throw new InvalidArgumentException('Malformed JSON body');
            }
        } catch (InvalidArgumentException $exception) {
            $this->cacheAuditLog('cache_delete_invalid_json', []);

            //return 400
            return $this->_responseFactory->error('Validation failed', 'CACHE-4008', $exception->getMessage(), [], 400);
        }

        //get key from body
        $key = $request->getBodyField('key');

        try {
            //Validate key
            if (!is_string($key) || $key === '') {
                throw new InvalidArgumentException('key is required and must be a string');
            }
        } catch (InvalidArgumentException $exception) {
            $this->cacheAuditLog('cache_delete_validation_failed', ['reason' => 'key missing or invalid']);

            //return 400
            return $this->_responseFactory->error('Validation failed', 'CACHE-4009', $exception->getMessage(), ['key' => 'required string'], 400);
        }

        try {
            //sanitize key now
            $key = trim($key);

            if ($key === '' || strlen($key) > self::CACHE_KEY_LENGTH_MAX || preg_match('/^[A-Za-z0-9._:-]+$/', $key) !== 1) {
                throw new InvalidArgumentException('key must be 1 to 255 chars and contain only A-Z a-z 0-9 dot underscore colon hyphen');
            }
        } catch (InvalidArgumentException $exception) {
            $this->cacheAuditLog('cache_delete_validation_failed', ['reason' => 'key format invalid']);

            //return 400
            return $this->_responseFactory->error('Validation failed', 'CACHE-4010', $exception->getMessage(), ['key' => 'invalid format'], 400);
        }

        //rate-limit placeholder
        if ($this->cacheDeleteRateLimitCheck($request) !== true) {
            $this->cacheAuditLog('cache_delete_rate_limit_failed', ['key' => $key]);
            return $this->_responseFactory->error('Rate limit exceeded', 'CACHE-4293', 'Too many requests', [], 429);
        }

        //idempotency placeholder
        if ($this->cacheDeleteIdempotencyCheck($request) !== true) {
            $this->cacheAuditLog('cache_delete_idempotency_failed', ['key' => $key]);
            return $this->_responseFactory->error('Duplicate request', 'CACHE-4093', 'Duplicate request detected', [], 409);
        }

        //delegation now
        $deleteResult = $this->_cacheService->delete($key);

        //audit success!!!!!!!
        $this->cacheAuditLog('cache_delete_success', ['key' => $key, 'deleted' => $deleteResult['deleted']]);

        //return success even if key was missing
        return $this->_responseFactory->success('Cache delete completed', [
            'key' => $key,
            'value' => $deleteResult['value'],
            'deleted' => $deleteResult['deleted']
        ], 200);
    }
}
