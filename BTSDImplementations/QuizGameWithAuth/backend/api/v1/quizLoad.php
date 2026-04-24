<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../params/quizLoadParams.php';
require_once __DIR__ . '/../../service/logging.php';
require_once __DIR__ . '/../../service/quizLoadService.php';


require_once __DIR__ . '/../../../database/repository/userRepository.php';
require_once __DIR__ . '/../../../database/repository/userPermissionRepository.php';
require_once __DIR__ . '/../../../database/repository/gameConfigRepository.php';

//this will tell the browser to treat the response as json data and not as html/text/anything else
header('Content-Type:application/json');

//AUTHENTICATE FUNCTION
function quizLoadAuthenticate(QuizLoadParams $params): bool //parameter is nullable int because when there is no quiz attempt id in the request then it will be null and when there is a quiz attempt id in the request then it will be an integer
{
    $uid=$params->getUid();

    if ($uid===null || $uid<=0) {
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
function quizLoadAuthorize(QuizLoadParams $params): bool
{
    $uid=$params->getUid();

    if ($uid===null || $uid<=0) {
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
function quizLoadRateLimitCheck(QuizLoadParams $params): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//IDEMPOTENCY FUNCTION
function quizLoadIdempotencyCheck(QuizLoadParams $params): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUDIT LOG FUNCTION
function quizLoadAuditLog(string $action,array $context): void
{
    Logger::logInfo('quizLoadApi',$action,$context); //we will log the action and the context of that action which will include the quiz attempt id and other relevant info
}

//MAIN BOUNDARY FUNCTION
function quizLoadHandle(): void //will return nothing
{

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(HTTP_STATUS_METHOD_NOT_ALLOWED);
        echo json_encode([
            'error'=>'Invalid request method,GET required!!'
        ]);

        quizLoadAuditLog('quiz_load_method_failed',[
            'method'=>$_SERVER['REQUEST_METHOD'] ?? null
        ]);
        exit;
    }

    /**
     1. AUTHENTICATE
     */
    $uid=null; //initialize id with null
    $gameConfigId=null; //new config id because quiz is now per config

    if (isset($_GET['uid'])) //take id from get request
    {
        //but all get-type values are directly strings so we will trim that string and then have to check if it is not empty and if it is numeric then only we will convert that to integer and assign to quiz attempt id otherwise we will keep quiz attempt id as null which means that there was some problem with the quiz attempt id in the request and we will handle that in the validation step later
        $uidRaw=trim((string)$_GET['uid']);

        //now if string is not empty or if string is numeric then we can just give it as the int value of it
        if ($uidRaw !== '' && is_numeric($uidRaw)) {
            $uid=(int)$uidRaw;
        }
    }

    if (isset($_GET['gameConfigId'])) {
        $gameConfigIdRaw=trim((string)$_GET['gameConfigId']);

        if ($gameConfigIdRaw !== '' && is_numeric($gameConfigIdRaw)) {
            $gameConfigId=(int)$gameConfigIdRaw;
        }
    }

    $quizLoadParams=new QuizLoadParams($uid,$gameConfigId);

    //now to authenticate directly we can use the function we defined above
    if (quizLoadAuthenticate($quizLoadParams) !== true) {
        //check failed just return back
        http_response_code(HTTP_STATUS_UNAUTHORIZED);
        echo json_encode([
            'error'=>'Authentication failed!!'
        ]);

        quizLoadAuditLog('quiz_load_authentication_failed',[
            'uid'=>$uid,
            'gameConfigId'=>$gameConfigId
        ]);
        exit;
    }

    /**
     2. AUTHORIZE
     */
    if (quizLoadAuthorize($quizLoadParams) !== true) {
        http_response_code(HTTP_STATUS_FORBIDDEN);
        echo json_encode([
            'error'=>'Authorization failed!!'
        ]);

        quizLoadAuditLog('quiz_load_authorization_failed',[
            'uid'=>$uid,
            'gameConfigId'=>$gameConfigId
        ]);
        exit;
    }

    /**
     3. VALIDATE
     4. SANITIZE
     */
    $uid=null; //re-initialize quiz attempt id to null because if there was some problem with the quiz attempt id in the request then we want to make sure that it is null and we will handle that in the validation step below and if there was no problem with the quiz attempt id in the request then we will assign the correct value to it in the validation step below and use that value for further processing
    $gameConfigId=null; //same for game config id

    if (!isset($_GET['uid'])) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'Invalid uid,it is required!!'
        ]);

        quizLoadAuditLog('quiz_load_validation_failed',[
            'reason'=>'uid missing'
        ]);
        exit;
    }

    if (isset($_GET['uid'])) //check if it is actually set
    {
        //will be in string format again
        $uidRaw=trim((string)$_GET['uid']);

        if ($uidRaw==='') {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'Invalid uid,it cannot be empty!!'
            ]);

            quizLoadAuditLog('quiz_load_validation_failed',[
                'reason'=>'uid empty'
            ]);
            exit;
        }

        //we don't have numerical id
        if (!is_numeric($uidRaw)) {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'Invalid uid,it should be numeric!!'
            ]);

            quizLoadAuditLog('quiz_load_validation_failed',[
                'reason'=>'uid non numeric'
            ]);
            exit;
        }

        $uid=(int)$uidRaw;

        if ($uid<=0) {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'Invalid uid,it should be positive number!!'
            ]);

            quizLoadAuditLog('quiz_load_validation_failed',[
                'reason'=>'uid non positive',
                'uid'=>$uid
            ]);
            exit;
        }
    }

    if (!isset($_GET['gameConfigId'])) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'Invalid gameConfigId,it is required!!'
        ]);

        quizLoadAuditLog('quiz_load_validation_failed',[
            'reason'=>'gameConfigId missing',
            'uid'=>$uid
        ]);
        exit;
    }

    $gameConfigIdRaw=trim((string)$_GET['gameConfigId']);

    if ($gameConfigIdRaw==='') {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'Invalid gameConfigId,it cannot be empty!!'
        ]);

        quizLoadAuditLog('quiz_load_validation_failed',[
            'reason'=>'gameConfigId empty',
            'uid'=>$uid
        ]);
        exit;
    }

    if (!is_numeric($gameConfigIdRaw)) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'Invalid gameConfigId,it should be numeric!!'
        ]);

        quizLoadAuditLog('quiz_load_validation_failed',[
            'reason'=>'gameConfigId non numeric',
            'uid'=>$uid
        ]);
        exit;
    }

    $gameConfigId=(int)$gameConfigIdRaw;

    if ($gameConfigId<=0) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'Invalid gameConfigId,it should be positive number!!'
        ]);

        quizLoadAuditLog('quiz_load_validation_failed',[
            'reason'=>'gameConfigId non positive',
            'uid'=>$uid,
            'gameConfigId'=>$gameConfigId
        ]);
        exit;
    }

    //boundary also checks config exists so service receives valid config id
    $gameConfigRepository=new GameConfigRepository();
    $gameConfigCurrent=$gameConfigRepository->getGameConfigFromId($gameConfigId);

    if ($gameConfigCurrent===null) {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error'=>'Invalid gameConfigId,config not found!!'
        ]);

        quizLoadAuditLog('quiz_load_validation_failed',[
            'reason'=>'gameConfigId not found',
            'uid'=>$uid,
            'gameConfigId'=>$gameConfigId
        ]);
        exit;
    }

    $quizLoadParams=new QuizLoadParams($uid,$gameConfigId);

    /**
     5. RATE LIMIT
     */
    if (quizLoadRateLimitCheck($quizLoadParams) !== true) {
        http_response_code(HTTP_STATUS_TOO_MANY_REQUESTS);
        echo json_encode([
            'error'=>'Rate limit exceeded!!'
        ]);

        quizLoadAuditLog('quiz_load_rate_limit_failed',[
            'uid'=>$uid,
            'gameConfigId'=>$gameConfigId
        ]);
        exit;
    }

    /**
     6. IDEMPOTENCY
     */
    if (quizLoadIdempotencyCheck($quizLoadParams) !== true) {
        http_response_code(HTTP_STATUS_CONFLICT);
        echo json_encode([
            'error'=>'Duplicate request detected!!'
        ]);

        quizLoadAuditLog('quiz_load_idempotency_failed',[
            'uid'=>$uid,
            'gameConfigId'=>$gameConfigId
        ]);
        exit;
    }

    /**
     7. DELEGATE
     NOW HERE WE WILL NOT WRITE BUSINESS LOGIC
     WE WILL CALL SERVICE CLASS FOR THAT
     */
    $quizLoadService=new QuizLoadService();
    $responseData=$quizLoadService->quizLoadService($uid,$gameConfigId);

    /**
     8. RESPOND
     */
    http_response_code(HTTP_STATUS_OK);
    echo json_encode($responseData);

    /**
     9. AUDIT-LOG
     */


    quizLoadAuditLog('quiz_load_success',[
        'uid'=>$responseData['uid'] ?? $uid,
        'gameConfigId'=>$responseData['gameConfigId'] ?? $gameConfigId,
        'isQuizDone'=>$responseData['isQuizDone'] ?? false
    ]); //in this ?? means that if for some reason quiz attempt id is not present in the response data then we will use the quiz attempt id that we got from the request and if for some reason is quiz done is not present in the response data then we will consider that as false and log that in the audit log
}

try {
    quizLoadHandle(); //put whole function in try block
} catch (InvalidArgumentException $exception) {
    //this means if any invalid argument is passed then catch this 
    http_response_code(HTTP_STATUS_BAD_REQUEST);
    echo json_encode([
        'error'=>'Invalid request input!!'
    ]);

    Logger::logWarn(
        'quizLoadApi',
        'Invalid argument while loading quiz!!',
        'INVALID_ARGUMENT',
        [
            'errorMessage'=>$exception->getMessage() //this will give the msg too
        ]
    );
} catch (RuntimeException $exception) {
    //this means if there is some error in the runtime like database connection error or something like that then catch this
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error'=>'Runtime failure while loading quiz!!'
    ]);

    Logger::logError('quizLoadApi','Runtime failure while loading quiz!!','QUIZ_LOAD_RUNTIME_FAILURE',$exception,[]);
} catch (Throwable $exception) {
    //this means if there is some unexpected error that we did not anticipate then catch this,works in all unknown cases
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error'=>'Unexpected server error while loading quiz!!'
    ]);

    Logger::logFatal('quizLoadApi','Unhandled exception while loading quiz!!','QUIZ_LOAD_UNHANDLED_EXCEPTION',$exception,[]);
}
