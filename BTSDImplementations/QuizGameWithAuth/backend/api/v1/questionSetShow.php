<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../service/logging.php';
require_once __DIR__ . '/../../service/questionSetShowService.php';

require_once __DIR__ . '/../../../database/repository/userRepository.php';
require_once __DIR__ . '/../../../database/repository/userPermissionRepository.php';

header('Content-Type:application/json');

//AUTHENTICATE FUNCTION
function questionSetShowAuthenticate(int $uid): bool
{
    if ($uid <= 0) {
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
function questionSetShowAuthorize(int $uid): bool
{
    if ($uid <= 0) {
        return false;
    }

    $userPermissionRepository = new UserPermissionRepository();
    $userPermissionCurrent = $userPermissionRepository->getUserPermissionFromUid($uid);

    if ($userPermissionCurrent === null) {
        return false;
    }

    if ($userPermissionCurrent->getPermissionGroup() !== USER_PERMISSION_GROUP_ADMIN) {
        return false;
    }

    return true;
}

//RATE LIMIT FUNCTION
function questionSetShowRateLimitCheck(int $uid): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//IDEMPOTENCY FUNCTION
function questionSetShowIdempotencyCheck(int $uid): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUDIT LOG FUNCTION
function questionSetShowAuditLog(string $action, array $context): void
{
    Logger::logInfo('questionSetShowApi', $action, $context);
}

//MAIN BOUNDARY FUNCTION
function questionSetShowHandle(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(HTTP_STATUS_METHOD_NOT_ALLOWED);
        echo json_encode([
            'error' => 'Only GET method is allowed!!'
        ]);

        questionSetShowAuditLog('question_set_show_validation_failed', [
            'reason' => 'invalid request method',
            'requestMethod' => $_SERVER['REQUEST_METHOD'] ?? ''
        ]);
        exit;
    }

    $uidRaw = isset($_GET['uid']) ? trim((string)$_GET['uid']) : '';
    $cursorRaw = isset($_GET['cursor']) ? trim((string)$_GET['cursor']) : '0';
    $limitRaw = isset($_GET['limit']) ? trim((string)$_GET['limit']) : '5';

    if ($uidRaw === '' || !is_numeric($uidRaw)) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'uid must be numeric!!'
        ]);

        questionSetShowAuditLog('question_set_show_validation_failed', [
            'reason' => 'uid invalid'
        ]);
        exit;
    }

    $uid = (int)$uidRaw;

    if ($uid <= 0) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'uid must be a positive integer!!'
        ]);

        questionSetShowAuditLog('question_set_show_validation_failed', [
            'reason' => 'uid non positive',
            'uid' => $uid
        ]);
        exit;
    }

    if (!is_numeric($cursorRaw)) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'cursor must be numeric!!'
        ]);

        questionSetShowAuditLog('question_set_show_validation_failed', [
            'reason' => 'cursor invalid'
        ]);
        exit;
    }

    if (!is_numeric($limitRaw)) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'limit must be numeric!!'
        ]);

        questionSetShowAuditLog('question_set_show_validation_failed', [
            'reason' => 'limit invalid'
        ]);
        exit;
    }

    $cursor = (int)$cursorRaw;
    $limit = (int)$limitRaw;

    if ($cursor < 0) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'cursor cannot be negative!!'
        ]);

        questionSetShowAuditLog('question_set_show_validation_failed', [
            'reason' => 'cursor negative',
            'cursor' => $cursor
        ]);
        exit;
    }

    if ($limit <= 0 || $limit > 20) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'limit must be between 1 and 20!!'
        ]);

        questionSetShowAuditLog('question_set_show_validation_failed', [
            'reason' => 'limit out of range',
            'limit' => $limit
        ]);
        exit;
    }

    if (questionSetShowAuthenticate($uid) !== true) {
        http_response_code(HTTP_STATUS_UNAUTHORIZED);
        echo json_encode([
            'error' => 'Authentication failed!!'
        ]);

        questionSetShowAuditLog('question_set_show_authentication_failed', [
            'uid' => $uid
        ]);
        exit;
    }

    if (questionSetShowAuthorize($uid) !== true) {
        http_response_code(HTTP_STATUS_FORBIDDEN);
        echo json_encode([
            'error' => 'Authorization failed!!'
        ]);

        questionSetShowAuditLog('question_set_show_authorization_failed', [
            'uid' => $uid
        ]);
        exit;
    }

    if (questionSetShowRateLimitCheck($uid) !== true) {
        http_response_code(HTTP_STATUS_TOO_MANY_REQUESTS);
        echo json_encode([
            'error' => 'Rate limit exceeded!!'
        ]);

        questionSetShowAuditLog('question_set_show_rate_limit_failed', [
            'uid' => $uid
        ]);
        exit;
    }

    if (questionSetShowIdempotencyCheck($uid) !== true) {
        http_response_code(HTTP_STATUS_CONFLICT);
        echo json_encode([
            'error' => 'Duplicate request detected!!'
        ]);

        questionSetShowAuditLog('question_set_show_idempotency_failed', [
            'uid' => $uid
        ]);
        exit;
    }

    $questionSetShowService = new QuestionSetShowService();
    $responseData = $questionSetShowService->questionSetShowService($cursor, $limit);

    http_response_code(HTTP_STATUS_OK);
    echo json_encode($responseData);

    questionSetShowAuditLog('question_set_show_success', [
        'uid' => $uid,
        'cursor' => $cursor,
        'limit' => $limit,
        'returnedGameConfigCount' => count($responseData['gameConfigs'] ?? [])
    ]);
}

try {
    questionSetShowHandle();
} catch (InvalidArgumentException $exception) {
    http_response_code(HTTP_STATUS_BAD_REQUEST);
    echo json_encode([
        'error' => 'Invalid request input!!'
    ]);

    Logger::logWarn(
        'questionSetShowApi',
        'Invalid argument while showing question sets!!',
        'INVALID_ARGUMENT',
        [
            'errorMessage' => $exception->getMessage()
        ]
    );
} catch (RuntimeException $exception) {
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error' => 'Runtime failure while showing question sets!!'
    ]);

    Logger::logError(
        'questionSetShowApi',
        'Runtime failure while showing question sets!!',
        'QUESTION_SET_SHOW_RUNTIME_FAILURE',
        $exception,
        []
    );
} catch (Throwable $exception) {
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error' => 'Unexpected server error while showing question sets!!'
    ]);

    Logger::logFatal(
        'questionSetShowApi',
        'Unhandled exception while showing question sets!!',
        'QUESTION_SET_SHOW_UNHANDLED_EXCEPTION',
        $exception,
        []
    );
}
