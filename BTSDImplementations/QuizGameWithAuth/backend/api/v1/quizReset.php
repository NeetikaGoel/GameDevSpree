<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../params/quizResetParams.php';
require_once __DIR__ . '/../../service/logging.php';
require_once __DIR__ . '/../../service/quizResetService.php';

require_once __DIR__ . '/../../../database/repository/userRepository.php';
require_once __DIR__ . '/../../../database/repository/userPermissionRepository.php';
require_once __DIR__ . '/../../../database/repository/gameConfigRepository.php';

//this will tell the browser to treat the response as json data and not as html/text/anything else
header('Content-Type:application/json');

//AUTHENTICATE FUNCTION
function quizResetAuthenticate(QuizResetParams $params): bool
{
    $uid = $params->getUid();

    if ($uid === null || $uid <= 0) {
        return false;
    }

    $userRepository = new UserRepository();
    $userCurrent = $userRepository->getUserFromUid($uid);

    if ($userCurrent === null) {
        return false;
    }

    return true;
}

//AUTHORIZE FUNCTION
function quizResetAuthorize(QuizResetParams $params): bool
{
    $uid = $params->getUid();

    if ($uid === null || $uid <= 0) {
        return false;
    }

    $userPermissionRepository = new UserPermissionRepository();
    $userPermissionCurrent = $userPermissionRepository->getUserPermissionFromUid($uid);

    if ($userPermissionCurrent === null) {
        return false;
    }

    $permissionGroup = $userPermissionCurrent->getPermissionGroup();

    if ($permissionGroup !== USER_PERMISSION_GROUP_GUEST && $permissionGroup !== USER_PERMISSION_GROUP_ADMIN && $permissionGroup !== USER_PERMISSION_GROUP_USER) {
        return false;
    }

    return true;
}

//RATE LIMIT FUNCTION
function quizResetRateLimitCheck(QuizResetParams $params): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//IDEMPOTENCY FUNCTION
function quizResetIdempotencyCheck(QuizResetParams $params): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUDIT LOG FUNCTION
function quizResetAuditLog(string $action, array $context): void
{
    Logger::logInfo('quizResetApi', $action, $context);
}

//MAIN BOUNDARY FUNCTION
function quizResetHandle(): void
{

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(HTTP_STATUS_METHOD_NOT_ALLOWED);
        echo json_encode([
            'error' => 'Invalid request method, POST required!!'
        ]);

        quizResetAuditLog('quiz_reset_method_failed', [
            'method' => $_SERVER['REQUEST_METHOD'] ?? null
        ]);
        exit;
    }
    /**
     * 
     * 
     * 
     * 
     1. AUTHENTICATE
     */
    $uid = null;
    $gameConfigId = null;

    if (isset($_POST['uid'])) {
        $uidRaw = trim((string)$_POST['uid']);

        if ($uidRaw !== '' && is_numeric($uidRaw)) {
            $uid = (int)$uidRaw;
        }
    }

    if (isset($_POST['gameConfigId'])) {
        $gameConfigIdRaw = trim((string)$_POST['gameConfigId']);

        if ($gameConfigIdRaw !== '' && is_numeric($gameConfigIdRaw)) {
            $gameConfigId = (int)$gameConfigIdRaw;
        }
    }

    $quizResetParams = new QuizResetParams($uid, $gameConfigId);

    if (quizResetAuthenticate($quizResetParams) !== true) {
        http_response_code(HTTP_STATUS_UNAUTHORIZED);
        echo json_encode([
            'error' => 'Authentication failed!!'
        ]);

        quizResetAuditLog('quiz_reset_authentication_failed', [
            'uid' => $uid,
            'gameConfigId' => $gameConfigId
        ]);
        exit;
    }

    /**
     2. AUTHORIZE
     */
    if (quizResetAuthorize($quizResetParams) !== true) {
        http_response_code(HTTP_STATUS_FORBIDDEN);
        echo json_encode([
            'error' => 'Authorization failed!!'
        ]);

        quizResetAuditLog('quiz_reset_authorization_failed', [
            'uid' => $uid,
            'gameConfigId' => $gameConfigId
        ]);
        exit;
    }

    /**
     3. VALIDATE
     4. SANITIZE
     */
    $uid = null;
    $gameConfigId = null;

    if (!isset($_POST['uid'])) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'Invalid uid, it is required!!'
        ]);

        quizResetAuditLog('quiz_reset_validation_failed', [
            'reason' => 'uid missing'
        ]);
        exit;
    }

    $uidRaw = trim((string)$_POST['uid']);

    if ($uidRaw === '') {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'Invalid uid, it cannot be empty!!'
        ]);

        quizResetAuditLog('quiz_reset_validation_failed', [
            'reason' => 'uid empty'
        ]);
        exit;
    }

    if (!is_numeric($uidRaw)) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'Invalid uid, it should be numeric!!'
        ]);

        quizResetAuditLog('quiz_reset_validation_failed', [
            'reason' => 'uid non numeric'
        ]);
        exit;
    }

    $uid = (int)$uidRaw;

    if ($uid <= 0) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'Invalid uid,it should be positive number!!'
        ]);

        quizResetAuditLog('quiz_reset_validation_failed', [
            'reason' => 'uid non positive',
            'uid' => $uid
        ]);
        exit;
    }

    if (!isset($_POST['gameConfigId'])) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'Invalid gameConfigId, it is required!!'
        ]);

        quizResetAuditLog('quiz_reset_validation_failed', [
            'reason' => 'gameConfigId missing',
            'uid' => $uid
        ]);
        exit;
    }

    $gameConfigIdRaw = trim((string)$_POST['gameConfigId']);

    if ($gameConfigIdRaw === '') {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'Invalid gameConfigId, it cannot be empty!!'
        ]);

        quizResetAuditLog('quiz_reset_validation_failed', [
            'reason' => 'gameConfigId empty',
            'uid' => $uid
        ]);
        exit;
    }

    if (!is_numeric($gameConfigIdRaw)) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'Invalid gameConfigId, it should be numeric!!'
        ]);

        quizResetAuditLog('quiz_reset_validation_failed', [
            'reason' => 'gameConfigId non numeric',
            'uid' => $uid
        ]);
        exit;
    }

    $gameConfigId = (int)$gameConfigIdRaw;

    if ($gameConfigId <= 0) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'Invalid gameConfigId,it should be positive number!!'
        ]);

        quizResetAuditLog('quiz_reset_validation_failed', [
            'reason' => 'gameConfigId non positive',
            'uid' => $uid,
            'gameConfigId' => $gameConfigId
        ]);
        exit;
    }

    $gameConfigRepository = new GameConfigRepository();
    $gameConfigCurrent = $gameConfigRepository->getGameConfigFromId($gameConfigId);

    //here we check that config really exists before reset service runs, otherwise reset would be on invalid config id
    if ($gameConfigCurrent === null) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'Invalid gameConfigId, config not found!!'
        ]);

        quizResetAuditLog('quiz_reset_validation_failed', [
            'reason' => 'gameConfigId not found',
            'uid' => $uid,
            'gameConfigId' => $gameConfigId
        ]);
        exit;
    }

    $quizResetParams = new QuizResetParams($uid, $gameConfigId);

    /**
     5. RATE LIMIT
     */
    if (quizResetRateLimitCheck($quizResetParams) !== true) {
        http_response_code(HTTP_STATUS_TOO_MANY_REQUESTS);
        echo json_encode([
            'error' => 'Rate limit exceeded!!'
        ]);

        quizResetAuditLog('quiz_reset_rate_limit_failed', [
            'uid' => $uid,
            'gameConfigId' => $gameConfigId
        ]);
        exit;
    }

    /**
     6. IDEMPOTENCY
     */
    if (quizResetIdempotencyCheck($quizResetParams) !== true) {
        http_response_code(HTTP_STATUS_CONFLICT);
        echo json_encode([
            'error' => 'Duplicate request detected!!'
        ]);

        quizResetAuditLog('quiz_reset_idempotency_failed', [
            'uid' => $uid,
            'gameConfigId' => $gameConfigId
        ]);
        exit;
    }

    /**
     7. DELEGATE
     NOW HERE WE WILL NOT WRITE BUSINESS LOGIC
     WE WILL CALL SERVICE CLASS FOR THAT
     */
    $quizResetService = new QuizResetService();
    $responseData = $quizResetService->quizResetService($uid, $gameConfigId);

    /**
     8. RESPOND
     */
    http_response_code(HTTP_STATUS_OK);
    echo json_encode($responseData);

    /**
     9. AUDIT-LOG
     */
    quizResetAuditLog('quiz_reset_success', [
        'uid' => $responseData['uid'] ?? $uid,
        'gameConfigId' => $responseData['gameConfigId'] ?? $gameConfigId,
        'isReset' => $responseData['isReset'] ?? false
    ]);
}

try {
    quizResetHandle();
} catch (InvalidArgumentException $exception) {
    http_response_code(HTTP_STATUS_BAD_REQUEST);
    echo json_encode([
        'error' => 'Invalid request input!!'
    ]);

    Logger::logWarn(
        'quizResetApi',
        'Invalid argument while resetting quiz!!',
        'INVALID_ARGUMENT',
        [
            'errorMessage' => $exception->getMessage()
        ]
    );
} catch (RuntimeException $exception) {
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error' => 'Runtime failure while resetting quiz!!'
    ]);

    Logger::logError('quizResetApi', 'Runtime failure while resetting quiz!!', 'QUIZ_RESET_RUNTIME_FAILURE', $exception, []);
} catch (Throwable $exception) {
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error' => 'Unexpected server error while resetting quiz!!'
    ]);

    Logger::logFatal('quizResetApi', 'Unhandled exception while resetting quiz!!', 'QUIZ_RESET_UNHANDLED_EXCEPTION', $exception, []);
}
