<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../params/quizResultShowParams.php';
require_once __DIR__ . '/../../service/logging.php';
require_once __DIR__ . '/../../service/quizResultShowService.php';

require_once __DIR__ . '/../../../database/repository/userRepository.php';
require_once __DIR__ . '/../../../database/repository/userPermissionRepository.php';
require_once __DIR__ . '/../../../database/repository/gameConfigRepository.php';

header('Content-Type:application/json');

//AUTHENTICATE FUNCTION
function quizResultAuthenticate(QuizResultShowParams $quizResultShowParams): bool
{
    $uid=$quizResultShowParams->getUid();

    if ($uid <= 0) {
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
function quizResultAuthorize(QuizResultShowParams $quizResultShowParams): bool
{
    $uid=$quizResultShowParams->getUid();

    if ($uid <= 0) {
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
function quizResultRateLimitCheck(QuizResultShowParams $quizResultShowParams): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//IDEMPOTENCY FUNCTION
function quizResultIdempotencyCheck(QuizResultShowParams $quizResultShowParams): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUDIT LOG FUNCTION
function quizResultAuditLog(string $action,array $context): void
{
    Logger::logInfo(
        'quizResultApi',
        $action,
        $context
    );
}

//MAIN BOUNDARY FUNCTION
function quizResultHandle(): void
{
    /**
     1. AUTHENTICATE
     */
    $uidTemp=0;
    $gameConfigIdTemp=0;

    if (isset($_GET['uid']) && is_numeric((string)$_GET['uid'])) {
        $uidTemp=(int)$_GET['uid'];
    }

    if (isset($_GET['gameConfigId']) && is_numeric((string)$_GET['gameConfigId'])) {
        $gameConfigIdTemp=(int)$_GET['gameConfigId'];
    }

    $quizResultShowParams=new QuizResultShowParams($uidTemp,$gameConfigIdTemp);

    if (quizResultAuthenticate($quizResultShowParams) !== true) {
        http_response_code(HTTP_STATUS_UNAUTHORIZED);
        echo json_encode([
            'error'=>'Authentication failed!!'
        ]);

        quizResultAuditLog('quiz_result_authentication_failed',[
            'uid'=>$uidTemp,
            'gameConfigId'=>$gameConfigIdTemp
        ]);
        exit;
    }

    /**
     2. AUTHORIZE
     */
    if (quizResultAuthorize($quizResultShowParams) !== true) {
        http_response_code(HTTP_STATUS_FORBIDDEN);
        echo json_encode([
            'error'=>'Authorization failed!!'
        ]);

        quizResultAuditLog('quiz_result_authorization_failed',[
            'uid'=>$uidTemp,
            'gameConfigId'=>$gameConfigIdTemp
        ]);
        exit;
    }

    /**
     3. VALIDATE
     4. SANITIZE
     */
    if (!isset($_GET['uid'])) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'uid is required!!'
        ]);

        quizResultAuditLog('quiz_result_validation_failed',[
            'reason'=>'uid missing'
        ]);
        exit;
    }

    $uidRaw=trim((string)$_GET['uid']);

    if ($uidRaw==='' || !is_numeric($uidRaw)) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'uid must be numeric!!'
        ]);

        quizResultAuditLog('quiz_result_validation_failed',[
            'reason'=>'uid invalid'
        ]);
        exit;
    }

    $uid=(int)$uidRaw;

    if ($uid <= 0) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'uid must be a positive integer!!'
        ]);

        quizResultAuditLog('quiz_result_validation_failed',[
            'reason'=>'uid non positive',
            'uid'=>$uid
        ]);
        exit;
    }

    if (!isset($_GET['gameConfigId'])) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'gameConfigId is required!!'
        ]);

        quizResultAuditLog('quiz_result_validation_failed',[
            'reason'=>'gameConfigId missing',
            'uid'=>$uid
        ]);
        exit;
    }

    $gameConfigIdRaw=trim((string)$_GET['gameConfigId']);

    if ($gameConfigIdRaw==='' || !is_numeric($gameConfigIdRaw)) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'gameConfigId must be numeric!!'
        ]);

        quizResultAuditLog('quiz_result_validation_failed',[
            'reason'=>'gameConfigId invalid',
            'uid'=>$uid
        ]);
        exit;
    }

    $gameConfigId=(int)$gameConfigIdRaw;

    if ($gameConfigId <= 0) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'gameConfigId must be a positive integer!!'
        ]);

        quizResultAuditLog('quiz_result_validation_failed',[
            'reason'=>'gameConfigId non positive',
            'uid'=>$uid,
            'gameConfigId'=>$gameConfigId
        ]);
        exit;
    }

    $gameConfigRepository=new GameConfigRepository();
    $gameConfigCurrent=$gameConfigRepository->getGameConfigFromId($gameConfigId);

    if ($gameConfigCurrent===null) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'Invalid gameConfigId,config not found!!'
        ]);

        quizResultAuditLog('quiz_result_validation_failed',[
            'reason'=>'gameConfigId not found',
            'uid'=>$uid,
            'gameConfigId'=>$gameConfigId
        ]);
        exit;
    }

    $quizResultShowParams=new QuizResultShowParams($uid,$gameConfigId);


    /**
     5. RATE LIMIT
     */
    if (quizResultRateLimitCheck($quizResultShowParams) !== true) {
        http_response_code(HTTP_STATUS_TOO_MANY_REQUESTS);
        echo json_encode([
            'error'=>'Rate limit exceeded!!'
        ]);

        quizResultAuditLog('quiz_result_rate_limit_failed',[
            'uid'=>$uid,
            'gameConfigId'=>$gameConfigId
        ]);
        exit;
    }

    /**
     6. IDEMPOTENCY
     */
    if (quizResultIdempotencyCheck($quizResultShowParams) !== true) {
        http_response_code(HTTP_STATUS_CONFLICT);
        echo json_encode([
            'error'=>'Duplicate request detected!!'
        ]);

        quizResultAuditLog('quiz_result_idempotency_failed',[
            'uid'=>$uid,
            'gameConfigId'=>$gameConfigId
        ]);
        exit;
    }

    /**
     7. DELEGATE
     SERVICE CLASS WILL DO THE BUSINESS LOGIC
     */
    $quizResultShowService=new QuizResultShowService();
    $responseData=$quizResultShowService->quizResultShowService($uid,$gameConfigId);

    /**
     8. RESPOND
     */
    http_response_code(HTTP_STATUS_OK);
    echo json_encode($responseData);

    /**
     9. AUDIT-LOG
     */
    quizResultAuditLog('quiz_result_success',[
        'uid'=>$uid,
        'gameConfigId'=>$gameConfigId,
        'score'=>$responseData['score'] ?? 0
    ]);
}

try {
    quizResultHandle();
} catch (InvalidArgumentException $exception) {
    http_response_code(HTTP_STATUS_BAD_REQUEST);
    echo json_encode([
        'error'=>'Invalid request input!!'
    ]);

    Logger::logWarn(
        'quizResultApi',
        'Invalid argument while loading result.',
        'INVALID_ARGUMENT',
        [
            'errorMessage'=>$exception->getMessage()
        ]
    );
} catch (RuntimeException $exception) {
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error'=>'Runtime failure while loading result!!'
    ]);

    Logger::logError('quizResultApi','Runtime failure while loading result.','QUIZ_RESULT_RUNTIME_FAILURE',$exception,[]);
} catch (Throwable $exception) {
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error'=>'Unexpected server error while loading result!!'
    ]);

    Logger::logFatal('quizResultApi','Unhandled exception while loading result.','QUIZ_RESULT_UNHANDLED_EXCEPTION',$exception,[]);
}
