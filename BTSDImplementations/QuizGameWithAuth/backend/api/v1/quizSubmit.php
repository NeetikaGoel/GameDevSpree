<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../params/quizSubmitParams.php';
require_once __DIR__ . '/../../service/logging.php';
require_once __DIR__ . '/../../service/quizSubmitService.php';

require_once __DIR__ . '/../../../database/repository/userRepository.php';
require_once __DIR__ . '/../../../database/repository/userPermissionRepository.php';
require_once __DIR__ . '/../../../database/repository/gameConfigRepository.php';

//same to tell the frontend that the response will be in json format
header('Content-Type:application/json');

//AUTHENTICATE FUNCTION
function quizSubmitAuthenticate(QuizSubmitParams $quizSubmitParams): bool
{
    $uid=$quizSubmitParams->getUid();

    if ($uid<=0) {
        return false;
    }

    $userRepository=new UserRepository();
    $userCurrent=$userRepository->getUserFromUid($uid);

    if ($userCurrent===null) {
        return false;
    }

    return true;
}

//AUTHORIZE FUNCTION
function quizSubmitAuthorize(QuizSubmitParams $quizSubmitParams): bool
{
    $uid=$quizSubmitParams->getUid();

    if ($uid<=0) {
        return false;
    }

    $userPermissionRepository=new UserPermissionRepository();
    $userPermissionCurrent=$userPermissionRepository->getUserPermissionFromUid($uid);

    if ($userPermissionCurrent===null) {
        return false;
    }

    $permissionGroup=$userPermissionCurrent->getPermissionGroup();

    if ($permissionGroup !== USER_PERMISSION_GROUP_GUEST && $permissionGroup !== USER_PERMISSION_GROUP_ADMIN && $permissionGroup !== USER_PERMISSION_GROUP_USER) {
        return false;
    }

    return true;
}

//RATE LIMIT FUNCTION
function quizSubmitRateLimitCheck(QuizSubmitParams $quizSubmitParams): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//IDEMPOTENCY FUNCTION
function quizSubmitIdempotencyCheck(QuizSubmitParams $quizSubmitParams): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUDIT LOG FUNCTION
function quizSubmitAuditLog(string $action, array $context): void
{
    Logger::logInfo('quizSubmitApi', $action, $context);
}

//MAIN BOUNDARY FUNCTION
function quizSubmitHandle(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(HTTP_STATUS_METHOD_NOT_ALLOWED);
        echo json_encode([
            'error' => 'Invalid request method, POST required!!'
        ]);

        quizSubmitAuditLog('quiz_submit_method_failed', [
            'method' => $_SERVER['REQUEST_METHOD'] ?? null
        ]);
        exit;
    }

    /**
     1. AUTHENTICATE
     */
    //temporary variables to hold the raw values from the request before validation and sanitization
    $uidTemp=0;
    $gameConfigIdTemp=0;
    $answerOptionIdByUserTemp=0;


    //its a post request method
    if (isset($_POST['uid']) && is_numeric((string)$_POST['uid'])) {
        $uidTemp=(int)$_POST['uid'];
    }

    if (isset($_POST['gameConfigId']) && is_numeric((string)$_POST['gameConfigId'])) {
        $gameConfigIdTemp=(int)$_POST['gameConfigId'];
    }

    if (isset($_POST['answerOptionId']) && is_numeric((string)$_POST['answerOptionId'])) {
        $answerOptionIdByUserTemp=(int)$_POST['answerOptionId'];
    }

    $quizSubmitParams=new QuizSubmitParams($uidTemp, $gameConfigIdTemp, $answerOptionIdByUserTemp);
    if (quizSubmitAuthenticate($quizSubmitParams) !== true) {
        http_response_code(HTTP_STATUS_UNAUTHORIZED);
        echo json_encode([
            'error' => 'Authentication failed!!'
        ]);

        quizSubmitAuditLog('quiz_submit_authentication_failed', [
            'uid' => $uidTemp,
            'gameConfigId' => $gameConfigIdTemp,
            'answerOptionIdByUser' => $answerOptionIdByUserTemp
        ]);
        exit;
    }

    /**
     2. AUTHORIZE
     */
    if (quizSubmitAuthorize($quizSubmitParams) !== true) {
        http_response_code(HTTP_STATUS_FORBIDDEN);
        echo json_encode([
            'error' => 'Authorization failed!!'
        ]);

        quizSubmitAuditLog('quiz_submit_authorization_failed', [
            'uid' => $uidTemp,
            'gameConfigId' => $gameConfigIdTemp,
            'answerOptionIdByUser' => $answerOptionIdByUserTemp
        ]);
        exit;
    }

    /**
     3. VALIDATE
     4. SANITIZE
     */
    if (!isset($_POST['uid']) || !isset($_POST['gameConfigId']) || !isset($_POST['answerOptionId'])) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'uid, gameConfigId and answerOptionId are required!!'
        ]);

        quizSubmitAuditLog('quiz_submit_validation_failed', [
            'reason' => 'required fields missing'
        ]);
        exit;
    }


    //all post requests will come in string format so we need to trim and then check if they are numeric and then convert them to int
    $uidRaw=trim((string)$_POST['uid']);
    $gameConfigIdRaw=trim((string)$_POST['gameConfigId']);
    $answerOptionIdByUserRaw=trim((string)$_POST['answerOptionId']);

    if ($uidRaw==='' || $gameConfigIdRaw==='' || $answerOptionIdByUserRaw==='' || !is_numeric($uidRaw) || !is_numeric($gameConfigIdRaw) || !is_numeric($answerOptionIdByUserRaw)) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'uid, gameConfigId and answerOptionId must be numeric!!'
        ]);

        quizSubmitAuditLog('quiz_submit_validation_failed', [
            'reason' => 'fields not numeric'
        ]);
        exit;
    }

    $uid=(int)$uidRaw;
    $gameConfigId=(int)$gameConfigIdRaw;
    $answerOptionIdByUser=(int)$answerOptionIdByUserRaw;

    if ($uid<=0 || $gameConfigId<=0 || $answerOptionIdByUser<=0) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'uid, gameConfigId and answerOptionId must be positive integers!!'
        ]);

        quizSubmitAuditLog('quiz_submit_validation_failed', [
            'reason' => 'fields non positive',
            'uid' => $uid,
            'gameConfigId' => $gameConfigId,
            'answerOptionIdByUser' => $answerOptionIdByUser
        ]);
        exit;
    }

    $gameConfigRepository=new GameConfigRepository();
    $gameConfigCurrent=$gameConfigRepository->getGameConfigFromId($gameConfigId);

    //submit must also ensure config exists so that answer submission is always tied to a valid config row
    if ($gameConfigCurrent===null) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'Invalid gameConfigId, config not found!!'
        ]);

        quizSubmitAuditLog('quiz_submit_validation_failed', [
            'reason' => 'gameConfigId not found',
            'uid' => $uid,
            'gameConfigId' => $gameConfigId,
            'answerOptionIdByUser' => $answerOptionIdByUser
        ]);
        exit;
    }

    $quizSubmitParams=new QuizSubmitParams($uid, $gameConfigId, $answerOptionIdByUser);

    /**
     5. RATE LIMIT
     */
    if (quizSubmitRateLimitCheck($quizSubmitParams) !== true) {
        http_response_code(HTTP_STATUS_TOO_MANY_REQUESTS);
        echo json_encode([
            'error' => 'Rate limit exceeded!!'
        ]);

        quizSubmitAuditLog('quiz_submit_rate_limit_failed', [
            'uid' => $uid,
            'gameConfigId' => $gameConfigId,
            'answerOptionIdByUser' => $answerOptionIdByUser
        ]);
        exit;
    }

    /**
     6. IDEMPOTENCY
     */
    if (quizSubmitIdempotencyCheck($quizSubmitParams) !== true) {
        http_response_code(HTTP_STATUS_CONFLICT);
        echo json_encode([
            'error' => 'Duplicate submit request detected!!'
        ]);

        quizSubmitAuditLog('quiz_submit_idempotency_failed', [
            'uid' => $uid,
            'gameConfigId' => $gameConfigId,
            'answerOptionIdByUser' => $answerOptionIdByUser
        ]);
        exit;
    }

    /**
     7. DELEGATE
     BUSINESS LOGIC WILL NOW COME FROM SERVICE CLASS
     */
    $quizSubmitService=new QuizSubmitService();
    $responseData=$quizSubmitService->quizSubmitService($uid, $gameConfigId, $answerOptionIdByUser);

    /**
     8. RESPOND
     */
    http_response_code(HTTP_STATUS_OK);
    echo json_encode($responseData);

    /**
     9. AUDIT-LOG
     */
    quizSubmitAuditLog('quiz_submit_success', [
        'uid' => $uid,
        'gameConfigId' => $gameConfigId,
        'answerOptionIdByUser' => $answerOptionIdByUser,
        'isQuizDone' => $responseData['isQuizDone'] ?? false
    ]);
}

try {
    quizSubmitHandle(); //put whole function in try block to catch any unexpected error that might occur anywhere in the code and handle it gracefully
} catch (InvalidArgumentException $exception) {
    http_response_code(HTTP_STATUS_BAD_REQUEST);
    echo json_encode([
        'error' => 'Invalid request input!!'
    ]);

    Logger::logWarn(
        'quizSubmitApi',
        'Invalid argument while submitting answer.',
        'INVALID_ARGUMENT',
        [
            'errorMessage' => $exception->getMessage()
        ]
    );
} catch (RuntimeException $exception) {
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error' => 'Runtime failure while submitting answer!!'
    ]);

    Logger::logError('quizSubmitApi', 'Runtime failure while submitting answer.', 'QUIZ_SUBMIT_RUNTIME_FAILURE', $exception, []);
} catch (Throwable $exception) {
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error' => 'Unexpected server error while submitting answer!!'
    ]);

    Logger::logFatal('quizSubmitApi', 'Unhandled exception while submitting answer.', 'QUIZ_SUBMIT_UNHANDLED_EXCEPTION', $exception, []);
}
