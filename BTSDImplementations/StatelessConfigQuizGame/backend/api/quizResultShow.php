<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../service/logging.php';
require_once __DIR__ . '/../service/quizResultShowService.php';

header('Content-Type:application/json');

//AUTHENTICATE FUNCTION
function quizResultAuthenticate(int $quizAttemptId):bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUTHORIZE FUNCTION
function quizResultAuthorize(int $quizAttemptId):bool
{
    return true; //PLACEHOLDER FOR NOW
}

//RATE LIMIT FUNCTION
function quizResultRateLimitCheck(int $quizAttemptId):bool
{
    return true; //PLACEHOLDER FOR NOW
}

//IDEMPOTENCY FUNCTION
function quizResultIdempotencyCheck(int $quizAttemptId):bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUDIT LOG FUNCTION
function quizResultAuditLog(string $action, array $context):void
{
    Logger::logInfo(
        'quizResultApi',
        $action,
        $context
    );
}

//MAIN BOUNDARY FUNCTION
function quizResultHandle():void
{
    /**
     1. AUTHENTICATE
     */
    $quizAttemptIdTemp=0;

    if (isset($_GET['quizAttemptId']) && is_numeric((string)$_GET['quizAttemptId']))
        {
            $quizAttemptIdTemp=(int)$_GET['quizAttemptId'];
        }

    if (quizResultAuthenticate($quizAttemptIdTemp)!==true)
        {
            http_response_code(HTTP_STATUS_UNAUTHORIZED);
            echo json_encode([
                'error'=>'Authentication failed!!'
            ]);

            quizResultAuditLog('quiz_result_authentication_failed', [
                'quizAttemptId'=>$quizAttemptIdTemp
            ]);
            exit;
        }

    /**
     2. AUTHORIZE
     */
    if (quizResultAuthorize($quizAttemptIdTemp)!==true)
        {
            http_response_code(HTTP_STATUS_FORBIDDEN);
            echo json_encode([
                'error'=>'Authorization failed!!'
            ]);

            quizResultAuditLog('quiz_result_authorization_failed', [
                'quizAttemptId'=>$quizAttemptIdTemp
            ]);
            exit;
        }

    /**
     3. VALIDATE
     4. SANITIZE
     */
    if (!isset($_GET['quizAttemptId']))
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'quizAttemptId is required!!'
            ]);

            quizResultAuditLog('quiz_result_validation_failed', [
                'reason'=>'quizAttemptId missing'
            ]);
            exit;
        }

    $quizAttemptIdRaw=trim((string)$_GET['quizAttemptId']);

    if ($quizAttemptIdRaw === '' || !is_numeric($quizAttemptIdRaw))
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'quizAttemptId must be numeric!!'
            ]);

            quizResultAuditLog('quiz_result_validation_failed', [
                'reason'=>'quizAttemptId invalid'
            ]);
            exit;
        }

    $quizAttemptId=(int)$quizAttemptIdRaw;

    if ($quizAttemptId<=0)
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'quizAttemptId must be a positive integer!!'
            ]);

            quizResultAuditLog('quiz_result_validation_failed', [
                'reason'=>'quizAttemptId non positive',
                'quizAttemptId'=>$quizAttemptId
            ]);
            exit;
        }

    /**
     5. RATE LIMIT
     */
    if (quizResultRateLimitCheck($quizAttemptId)!==true)
        {
            http_response_code(HTTP_STATUS_TOO_MANY_REQUESTS);
            echo json_encode([
                'error'=>'Rate limit exceeded!!'
            ]);

            quizResultAuditLog('quiz_result_rate_limit_failed', [
                'quizAttemptId'=>$quizAttemptId
            ]);
            exit;
        }

    /**
     6. IDEMPOTENCY
     */
    if (quizResultIdempotencyCheck($quizAttemptId)!==true)
        {
            http_response_code(HTTP_STATUS_CONFLICT);
            echo json_encode([
                'error'=>'Duplicate request detected!!'
            ]);

            quizResultAuditLog('quiz_result_idempotency_failed', [
                'quizAttemptId'=>$quizAttemptId
            ]);
            exit;
        }

    /**
     7. DELEGATE
     SERVICE CLASS WILL DO THE BUSINESS LOGIC
     */
    $quizResultShowService=new QuizResultShowService();
    $responseData=$quizResultShowService->quizResultShowService($quizAttemptId);

    /**
     8. RESPOND
     */
    http_response_code(HTTP_STATUS_OK);
    echo json_encode($responseData);

    /**
     9. AUDIT-LOG
     */
    quizResultAuditLog('quiz_result_success', [
        'quizAttemptId'=>$quizAttemptId,
        'score'=>$responseData['score'] ?? 0
    ]);
}

try
{
    quizResultHandle();
}


catch (InvalidArgumentException $exception)
{
    http_response_code(HTTP_STATUS_BAD_REQUEST);
    echo json_encode([
        'error'=>'Invalid request input!!'
    ]);

    Logger::logWarn('quizResultApi','Invalid argument while loading result.','INVALID_ARGUMENT',
        [
            'errorMessage'=>$exception->getMessage()
        ]
    );
}


catch (RuntimeException $exception)
{
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error'=>'Runtime failure while loading result!!'
    ]);

    Logger::logError('quizResultApi','Runtime failure while loading result.','QUIZ_RESULT_RUNTIME_FAILURE',$exception,[]);
}


catch (Throwable $exception)
{
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error'=>'Unexpected server error while loading result!!'
    ]);

    Logger::logFatal('quizResultApi','Unhandled exception while loading result.','QUIZ_RESULT_UNHANDLED_EXCEPTION',$exception,[]);
}
?>