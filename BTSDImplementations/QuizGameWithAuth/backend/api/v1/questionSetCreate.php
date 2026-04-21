<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../service/logging.php';
require_once __DIR__ . '/../service/questionSetCreateService.php';

require_once __DIR__ . '/../../database/repository/userRepository.php';
require_once __DIR__ . '/../../database/repository/userPermissionRepository.php';

header('Content-Type:application/json');

//AUTHENTICATE FUNCTION
function questionSetCreateAuthenticate(int $uid):bool
{
    if ($uid<=0)
        {
            return false;
        }

    $userRepository=new UserRepository();
    $userCurrent=$userRepository->getUserFromUid($uid);

    if ($userCurrent===null)
        {
            return false;
        }

    return true;
}

//AUTHORIZE FUNCTION
function questionSetCreateAuthorize(int $uid):bool
{
    if ($uid<=0)
        {
            return false;
        }

    $userPermissionRepository=new UserPermissionRepository();
    $userPermissionCurrent=$userPermissionRepository->getUserPermissionFromUid($uid);

    if ($userPermissionCurrent===null)
        {
            return false;
        }
    
    //ONLY ADMIN IS REQUIRED TO ACCESS IT!!!!!
    if ($userPermissionCurrent->getPermissionGroup()!==USER_PERMISSION_GROUP_ADMIN)
        {
            return false;
        }

    return true;
}

//RATE LIMIT FUNCTION
function questionSetCreateRateLimitCheck(int $uid):bool
{
    return true; //PLACEHOLDER FOR NOW
}

//IDEMPOTENCY FUNCTION
function questionSetCreateIdempotencyCheck(int $uid):bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUDIT LOG FUNCTION
function questionSetCreateAuditLog(string $action,array $context):void
{
    Logger::logInfo('questionSetCreateApi',$action,$context);
}

//MAIN BOUNDARY FUNCTION
function questionSetCreateHandle():void
{
    if ($_SERVER['REQUEST_METHOD']!=='POST')
        {
            http_response_code(HTTP_STATUS_METHOD_NOT_ALLOWED);
            echo json_encode([
                'error'=>'Only POST method is allowed!!'
            ]);

            questionSetCreateAuditLog('question_set_create_validation_failed',[
                'reason'=>'invalid request method',
                'requestMethod'=>$_SERVER['REQUEST_METHOD'] ?? ''
            ]);
            exit;
        }

        
    /**
     1. AUTHENTICATE
     */
    $uidTemp=0;

    if (isset($_POST['uid']) && is_numeric((string)$_POST['uid']))
        {
            $uidTemp=(int)$_POST['uid'];
        }

    if (questionSetCreateAuthenticate($uidTemp)!==true)
        {
            http_response_code(HTTP_STATUS_UNAUTHORIZED);
            echo json_encode([
                'error'=>'Authentication failed!!'
            ]);

            questionSetCreateAuditLog('question_set_create_authentication_failed',[
                'uid'=>$uidTemp
            ]);
            exit;
        }

    /**
     2. AUTHORIZE
     */
    if (questionSetCreateAuthorize($uidTemp)!==true)
        {
            http_response_code(HTTP_STATUS_FORBIDDEN);
            echo json_encode([
                'error'=>'Authorization failed!!'
            ]);

            questionSetCreateAuditLog('question_set_create_authorization_failed',[
                'uid'=>$uidTemp
            ]);
            exit;
        }

    /**
     3. VALIDATE
     4. SANITIZE
     */
    if (
        !isset($_POST['uid']) ||
        !isset($_POST['gameConfigName']) ||
        !isset($_POST['questionCountTarget']) ||
        !isset($_POST['questionIdListAllowed']) ||
        !isset($_POST['secretKey'])
    )
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'uid, gameConfigName, questionCountTarget, questionIdListAllowed and secretKey are required!!'
            ]);

            questionSetCreateAuditLog('question_set_create_validation_failed',[
                'reason'=>'required fields missing'
            ]);
            exit;
        }

    $uidRaw=trim((string)$_POST['uid']);
    $gameConfigName=trim((string)$_POST['gameConfigName']);
    $questionCountTargetRaw=trim((string)$_POST['questionCountTarget']);
    $questionIdListAllowedRaw=(string)$_POST['questionIdListAllowed'];
    $secretKey=trim((string)$_POST['secretKey']);

    if ($uidRaw==='' || !is_numeric($uidRaw))
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'uid must be numeric!!'
            ]);

            questionSetCreateAuditLog('question_set_create_validation_failed',[
                'reason'=>'uid invalid'
            ]);
            exit;
        }

    $uid=(int)$uidRaw;

    if ($uid<=0)
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'uid must be a positive integer!!'
            ]);

            questionSetCreateAuditLog('question_set_create_validation_failed',[
                'reason'=>'uid non positive',
                'uid'=>$uid
            ]);
            exit;
        }

    if ($gameConfigName==='' || $questionCountTargetRaw==='' || $questionIdListAllowedRaw==='' || $secretKey==='')
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'gameConfigName, questionCountTarget, questionIdListAllowed and secretKey cannot be empty!!'
            ]);

            questionSetCreateAuditLog('question_set_create_validation_failed',[
                'reason'=>'empty payload fields'
            ]);
            exit;
        }

    if (!is_numeric($questionCountTargetRaw))
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'questionCountTarget must be numeric!!'
            ]);

            questionSetCreateAuditLog('question_set_create_validation_failed',[
                'reason'=>'question count target invalid'
            ]);
            exit;
        }

    $questionCountTarget=(int)$questionCountTargetRaw;

    if ($questionCountTarget<=0)
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'questionCountTarget must be a positive integer!!'
            ]);

            questionSetCreateAuditLog('question_set_create_validation_failed',[
                'reason'=>'question count target non positive',
                'questionCountTarget'=>$questionCountTarget
            ]);
            exit;
        }

    $questionIdListAllowed=json_decode($questionIdListAllowedRaw,true);

    if (!is_array($questionIdListAllowed) || count($questionIdListAllowed)===0)
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'questionIdListAllowed must be a valid non-empty json array!!'
            ]);

            questionSetCreateAuditLog('question_set_create_validation_failed',[
                'reason'=>'question id list allowed invalid json'
            ]);
            exit;
        }

    foreach ($questionIdListAllowed as $questionIdCurrent)
        {
            if (!is_numeric((string)$questionIdCurrent) || (int)$questionIdCurrent<=0)
                {
                    http_response_code(HTTP_STATUS_BAD_REQUEST);
                    echo json_encode([
                        'error'=>'Each question id must be a positive integer!!'
                    ]);

                    questionSetCreateAuditLog('question_set_create_validation_failed',[
                        'reason'=>'question id invalid'
                    ]);
                    exit;
                }
        }

    /**
     5. RATE LIMIT
     */
    if (questionSetCreateRateLimitCheck($uid)!==true)
        {
            http_response_code(HTTP_STATUS_TOO_MANY_REQUESTS);
            echo json_encode([
                'error'=>'Rate limit exceeded!!'
            ]);

            questionSetCreateAuditLog('question_set_create_rate_limit_failed',[
                'uid'=>$uid
            ]);
            exit;
        }

    /**
     6. IDEMPOTENCY
     */
    if (questionSetCreateIdempotencyCheck($uid)!==true)
        {
            http_response_code(HTTP_STATUS_CONFLICT);
            echo json_encode([
                'error'=>'Duplicate request detected!!'
            ]);

            questionSetCreateAuditLog('question_set_create_idempotency_failed',[
                'uid'=>$uid
            ]);
            exit;
        }

    /**
     7. DELEGATE
     */
    $questionSetCreateService=new QuestionSetCreateService();
    $responseData=$questionSetCreateService->questionSetCreateService(
        $gameConfigName,
        $questionCountTarget,
        $questionIdListAllowed,
        $secretKey
    );

    /**
     8. RESPOND
     */
    http_response_code(HTTP_STATUS_OK);
    echo json_encode($responseData);

    /**
     9. AUDIT-LOG
     */
    questionSetCreateAuditLog('question_set_create_success',[
        'uid'=>$uid,
        'gameConfigId'=>$responseData['gameConfigId'] ?? 0,
        'gameConfigName'=>$responseData['gameConfigName'] ?? ''
    ]);
}

try
{
    questionSetCreateHandle();
}
catch (InvalidArgumentException $exception)
{
    http_response_code(HTTP_STATUS_BAD_REQUEST);
    echo json_encode([
        'error'=>'Invalid request input!!'
    ]);

    Logger::logWarn(
        'questionSetCreateApi',
        'Invalid argument while creating a new question set!!',
        'INVALID_ARGUMENT',
        [
            'errorMessage'=>$exception->getMessage()
        ]
    );
}
catch (RuntimeException $exception)
{
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error'=>'Runtime failure while creating a new question set!!'
    ]);

    Logger::logError(
        'questionSetCreateApi',
        'Runtime failure while creating a new question set!!',
        'QUESTION_SET_CREATE_RUNTIME_FAILURE',
        $exception,
        []
    );
}
catch (Throwable $exception)
{
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error'=>'Unexpected server error while creating a new question set!!'
    ]);

    Logger::logFatal(
        'questionSetCreateApi',
        'Unhandled exception while creating a new question set!!',
        'QUESTION_SET_CREATE_UNHANDLED_EXCEPTION',
        $exception,
        []
    );
}
?>