<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../service/logging.php';
require_once __DIR__ . '/../service/quizLoadService.php';

//this will tell the browser to treat the response as json data and not as html/text/anything else
header('Content-Type:application/json');

//AUTHENTICATE FUNCTION
function quizLoadAuthenticate(?int $quizAttemptId):bool //parameter is nullable int because when there is no quiz attempt id in the request then it will be null and when there is a quiz attempt id in the request then it will be an integer
{
    return true; //PLACEHOLDER FOR NOW
}

//AUTHORIZE FUNCTION
function quizLoadAuthorize(?int $quizAttemptId):bool
{
    return true; //PLACEHOLDER FOR NOW
}

//RATE LIMIT FUNCTION
function quizLoadRateLimitCheck(?int $quizAttemptId):bool
{
    return true; //PLACEHOLDER FOR NOW
}

//IDEMPOTENCY FUNCTION
function quizLoadIdempotencyCheck(?int $quizAttemptId):bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUDIT LOG FUNCTION
function quizLoadAuditLog(string $action,array $context):void
{
    Logger::logInfo('quizLoadApi',$action,$context); //we will log the action and the context of that action which will include the quiz attempt id and other relevant info
}

//MAIN BOUNDARY FUNCTION
function quizLoadHandle():void //will return nothing
{
    /**
     1. AUTHENTICATE
     */
    $quizAttemptId=null; //initialize id with null

    if (isset($_GET['quizAttemptId'])) //take id from get request
        {
            //but all get-type values are directly strings so we will trim that string and then have to check if it is not empty and if it is numeric then only we will convert that to integer and assign to quiz attempt id otherwise we will keep quiz attempt id as null which means that there was some problem with the quiz attempt id in the request and we will handle that in the validation step later
            $quizAttemptIdRaw=trim((string)$_GET['quizAttemptId']);

            //now if string is not empty or if string is numeric then we can just give it as the int value of it
            if ($quizAttemptIdRaw!=='' && is_numeric($quizAttemptIdRaw))
                {
                    $quizAttemptId=(int)$quizAttemptIdRaw;
                }
        }

    //now to authenticate directly we can use the function we defined above
    if (quizLoadAuthenticate($quizAttemptId)!==true)
        {
            //check failed just return back
            http_response_code(HTTP_STATUS_UNAUTHORIZED);
            echo json_encode([
                'error'=>'Authentication failed!!'
            ]);

            quizLoadAuditLog('quiz_load_authentication_failed',[
                'quizAttemptId'=>$quizAttemptId
            ]);
            exit;
        }

    /**
     2. AUTHORIZE
     */
    if (quizLoadAuthorize($quizAttemptId)!==true)
        {
            http_response_code(HTTP_STATUS_FORBIDDEN);
            echo json_encode([
                'error'=>'Authorization failed!!'
            ]);

            quizLoadAuditLog('quiz_load_authorization_failed',[
                'quizAttemptId'=>$quizAttemptId
            ]);
            exit;
        }

    /**
     3. VALIDATE
     4. SANITIZE
     */
    $quizAttemptId=null; //re-initialize quiz attempt id to null because if there was some problem with the quiz attempt id in the request then we want to make sure that it is null and we will handle that in the validation step below and if there was no problem with the quiz attempt id in the request then we will assign the correct value to it in the validation step below and use that value for further processing

    if (isset($_GET['quizAttemptId'])) //check if it is actually set
        {
            //will be in string format again
            $quizAttemptIdRaw=trim((string)$_GET['quizAttemptId']);

            if ($quizAttemptIdRaw==='')
                {
                    http_response_code(HTTP_STATUS_BAD_REQUEST);
                    echo json_encode([
                        'error'=>'Invalid attempt id, it cannot be empty!!'
                    ]);

                    quizLoadAuditLog('quiz_load_validation_failed',[
                        'reason'=>'quizAttemptId empty'
                    ]);
                    exit;
                }

            //we don't have numerical id
            if (!is_numeric($quizAttemptIdRaw))
                {
                    http_response_code(HTTP_STATUS_BAD_REQUEST);
                    echo json_encode([
                        'error'=>'Invalid attempt id, it should be numeric!!'
                    ]);

                    quizLoadAuditLog('quiz_load_validation_failed',[
                        'reason'=>'quizAttemptId non numeric'
                    ]);
                    exit;
                }

            $quizAttemptId=(int)$quizAttemptIdRaw;

            if ($quizAttemptId<=0)
                {
                    http_response_code(400);
                    echo json_encode([
                        'error'=>'Invalid attempt id,it should be positive number!!'
                    ]);

                    quizLoadAuditLog('quiz_load_validation_failed',[
                        'reason'=>'quizAttemptId non positive',
                        'quizAttemptId'=>$quizAttemptId
                    ]);
                    exit;
                }
        }

    /**
     5. RATE LIMIT
     */
    if (quizLoadRateLimitCheck($quizAttemptId)!==true)
        {
            http_response_code(HTTP_STATUS_TOO_MANY_REQUESTS);
            echo json_encode([
                'error'=>'Rate limit exceeded!!'
            ]);

            quizLoadAuditLog('quiz_load_rate_limit_failed',[
                'quizAttemptId'=>$quizAttemptId
            ]);
            exit;
        }

    /**
     6. IDEMPOTENCY
     */
    if (quizLoadIdempotencyCheck($quizAttemptId)!==true)
        {
            http_response_code(HTTP_STATUS_CONFLICT);
            echo json_encode([
                'error'=>'Duplicate request detected!!'
            ]);

            quizLoadAuditLog('quiz_load_idempotency_failed',[
                'quizAttemptId'=>$quizAttemptId
            ]);
            exit;
        }

    /**
     7. DELEGATE
     NOW HERE WE WILL NOT WRITE BUSINESS LOGIC
     WE WILL CALL SERVICE CLASS FOR THAT
     */
    $quizLoadService=new QuizLoadService();
    $responseData=$quizLoadService->quizLoadService($quizAttemptId);
    
    /**
     8. RESPOND
     */
    http_response_code(HTTP_STATUS_OK);
    echo json_encode($responseData);

    /**
     9. AUDIT-LOG
     */

    
    quizLoadAuditLog('quiz_load_success',[
        'quizAttemptId'=>$responseData['quizAttemptId'] ?? $quizAttemptId,
        'isQuizDone'=>$responseData['isQuizDone'] ?? false
    ]); //in this ?? means that if for some reason quiz attempt id is not present in the response data then we will use the quiz attempt id that we got from the request and if for some reason is quiz done is not present in the response data then we will consider that as false and log that in the audit log
}

try
{
    quizLoadHandle(); //put whole function in try block
}
catch (InvalidArgumentException $exception)
{
    //this means if any invalid argument is passed then catch this 
    http_response_code(HTTP_STATUS_BAD_REQUEST);
    echo json_encode([
        'error'=>'Invalid request input!!'
    ]);

    Logger::logWarn('quizLoadApi','Invalid argument while loading quiz!!','INVALID_ARGUMENT',
        [
            'errorMessage'=>$exception->getMessage() //this will give the msg too
        ]
    );
}


catch (RuntimeException $exception)
{
    //this means if there is some error in the runtime like database connection error or something like that then catch this
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error'=>'Runtime failure while loading quiz!!'
    ]);

    Logger::logError('quizLoadApi','Runtime failure while loading quiz!!','QUIZ_LOAD_RUNTIME_FAILURE',$exception,[]);
}


catch (Throwable $exception)
{
    //this means if there is some unexpected error that we did not anticipate then catch this, works in all unknown cases
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error'=>'Unexpected server error while loading quiz!!'
    ]);

    Logger::logFatal('quizLoadApi','Unhandled exception while loading quiz!!','QUIZ_LOAD_UNHANDLED_EXCEPTION',$exception,[]);
}
?>