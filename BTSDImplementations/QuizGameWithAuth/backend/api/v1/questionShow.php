<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../service/logging.php';
require_once __DIR__ . '/../../service/questionShowService.php';

require_once __DIR__ . '/../../../database/repository/userRepository.php';
require_once __DIR__ . '/../../../database/repository/userPermissionRepository.php';

header('Content-Type:application/json');

//AUTHENTICATE FUNCTION
function questionShowAuthenticate(int $uid): bool
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
function questionShowAuthorize(int $uid): bool
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
function questionShowRateLimitCheck(int $uid): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//IDEMPOTENCY FUNCTION
function questionShowIdempotencyCheck(int $uid): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUDIT LOG FUNCTION
function questionShowAuditLog(string $action, array $context): void
{
    Logger::logInfo('questionShowApi', $action, $context);
}

//MAIN BOUNDARY FUNCTION
function questionShowHandle(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(HTTP_STATUS_METHOD_NOT_ALLOWED);
        echo json_encode([
            'error' => 'Only GET method is allowed!!'
        ]);

        questionShowAuditLog('question_show_validation_failed', [
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

        questionShowAuditLog('question_show_validation_failed', [
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

        questionShowAuditLog('question_show_validation_failed', [
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

        questionShowAuditLog('question_show_validation_failed', [
            'reason' => 'cursor invalid'
        ]);
        exit;
    }

    if (!is_numeric($limitRaw)) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'limit must be numeric!!'
        ]);

        questionShowAuditLog('question_show_validation_failed', [
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

        questionShowAuditLog('question_show_validation_failed', [
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

        questionShowAuditLog('question_show_validation_failed', [
            'reason' => 'limit out of range',
            'limit' => $limit
        ]);
        exit;
    }

    if (questionShowAuthenticate($uid) !== true) {
        http_response_code(HTTP_STATUS_UNAUTHORIZED);
        echo json_encode([
            'error' => 'Authentication failed!!'
        ]);

        questionShowAuditLog('question_show_authentication_failed', [
            'uid' => $uid
        ]);
        exit;
    }

    if (questionShowAuthorize($uid) !== true) {
        http_response_code(HTTP_STATUS_FORBIDDEN);
        echo json_encode([
            'error' => 'Authorization failed!!'
        ]);

        questionShowAuditLog('question_show_authorization_failed', [
            'uid' => $uid
        ]);
        exit;
    }

    if (questionShowRateLimitCheck($uid) !== true) {
        http_response_code(HTTP_STATUS_TOO_MANY_REQUESTS);
        echo json_encode([
            'error' => 'Rate limit exceeded!!'
        ]);

        questionShowAuditLog('question_show_rate_limit_failed', [
            'uid' => $uid
        ]);
        exit;
    }

    if (questionShowIdempotencyCheck($uid) !== true) {
        http_response_code(HTTP_STATUS_CONFLICT);
        echo json_encode([
            'error' => 'Duplicate request detected!!'
        ]);

        questionShowAuditLog('question_show_idempotency_failed', [
            'uid' => $uid
        ]);
        exit;
    }

    $questionShowService = new QuestionShowService();
    $responseData = $questionShowService->questionShowService($cursor, $limit);

    http_response_code(HTTP_STATUS_OK);
    echo json_encode($responseData);

    questionShowAuditLog('question_show_success', [
        'uid' => $uid,
        'cursor' => $cursor,
        'limit' => $limit,
        'returnedQuestionCount' => count($responseData['questions'] ?? [])
    ]);
}

try {
    questionShowHandle();
} catch (InvalidArgumentException $exception) {
    http_response_code(HTTP_STATUS_BAD_REQUEST);
    echo json_encode([
        'error' => 'Invalid request input!!'
    ]);

    Logger::logWarn(
        'questionShowApi',
        'Invalid argument while showing questions!!',
        'INVALID_ARGUMENT',
        [
            'errorMessage' => $exception->getMessage()
        ]
    );
} catch (RuntimeException $exception) {
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error' => 'Runtime failure while showing questions!!'
    ]);

    Logger::logError(
        'questionShowApi',
        'Runtime failure while showing questions!!',
        'QUESTION_SHOW_RUNTIME_FAILURE',
        $exception,
        []
    );
} catch (Throwable $exception) {
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error' => 'Unexpected server error while showing questions!!'
    ]);

    Logger::logFatal(
        'questionShowApi',
        'Unhandled exception while showing questions!!',
        'QUESTION_SHOW_UNHANDLED_EXCEPTION',
        $exception,
        []
    );
}
