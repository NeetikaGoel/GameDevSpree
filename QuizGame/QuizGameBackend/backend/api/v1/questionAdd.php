<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../service/logging.php';
require_once __DIR__ . '/../../service/questionAddService.php';

require_once __DIR__ . '/../../../database/repository/userRepository.php';
require_once __DIR__ . '/../../../database/repository/userPermissionRepository.php';

header('Content-Type:application/json');

//AUTHENTICATE FUNCTION
function questionAddAuthenticate(int $uid):bool
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
function questionAddAuthorize(int $uid):bool
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
function questionAddRateLimitCheck(int $uid):bool
{
    return true; //PLACEHOLDER FOR NOW
}

//IDEMPOTENCY FUNCTION
function questionAddIdempotencyCheck(int $uid):bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUDIT LOG FUNCTION
function questionAddAuditLog(string $action,array $context):void
{
    Logger::logInfo('questionAddApi',$action,$context);
}

//MAIN BOUNDARY FUNCTION
function questionAddHandle():void
{
    if ($_SERVER['REQUEST_METHOD']!=='POST')
        {
            http_response_code(HTTP_STATUS_METHOD_NOT_ALLOWED);
            echo json_encode([
                'error'=>'Only POST method is allowed!!'
            ]);

            questionAddAuditLog('question_add_validation_failed',[
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

    if (questionAddAuthenticate($uidTemp)!==true)
        {
            http_response_code(HTTP_STATUS_UNAUTHORIZED);
            echo json_encode([
                'error'=>'Authentication failed!!'
            ]);

            questionAddAuditLog('question_add_authentication_failed',[
                'uid'=>$uidTemp
            ]);
            exit;
        }

    /**
     2. AUTHORIZE
     */
    if (questionAddAuthorize($uidTemp)!==true)
        {
            http_response_code(HTTP_STATUS_FORBIDDEN);
            echo json_encode([
                'error'=>'Authorization failed!!'
            ]);

            questionAddAuditLog('question_add_authorization_failed',[
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
        !isset($_POST['questionText']) ||
        !isset($_POST['questionType']) ||
        !isset($_POST['answerOptions'])
    )
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'uid, questionText, questionType and answerOptions are required!!'
            ]);

            questionAddAuditLog('question_add_validation_failed',[
                'reason'=>'required fields missing'
            ]);
            exit;
        }

    $uidRaw=trim((string)$_POST['uid']);
    $questionText=trim((string)$_POST['questionText']);
    $questionType=trim((string)$_POST['questionType']);
    $answerOptionsRaw=(string)$_POST['answerOptions'];

    if ($uidRaw==='' || !is_numeric($uidRaw))
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'uid must be numeric!!'
            ]);

            questionAddAuditLog('question_add_validation_failed',[
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

            questionAddAuditLog('question_add_validation_failed',[
                'reason'=>'uid non positive',
                'uid'=>$uid
            ]);
            exit;
        }

    if ($questionText==='' || $questionType==='' || $answerOptionsRaw==='')
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'questionText, questionType and answerOptions cannot be empty!!'
            ]);

            questionAddAuditLog('question_add_validation_failed',[
                'reason'=>'empty payload fields'
            ]);
            exit;
        }

    $answerOptions=json_decode($answerOptionsRaw,true);

    if (!is_array($answerOptions) || count($answerOptions)===0)
        {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error'=>'answerOptions must be a valid non-empty json array!!'
            ]);

            questionAddAuditLog('question_add_validation_failed',[
                'reason'=>'answer options invalid json'
            ]);
            exit;
        }

    foreach ($answerOptions as $answerOption)
        {
            if (
                !is_array($answerOption) ||
                !isset($answerOption['text']) ||
                !isset($answerOption['type']) ||
                !array_key_exists('isCorrect',$answerOption)
            )
                {
                    http_response_code(HTTP_STATUS_BAD_REQUEST);
                    echo json_encode([
                        'error'=>'Each answer option must contain text, type and isCorrect!!'
                    ]);

                    questionAddAuditLog('question_add_validation_failed',[
                        'reason'=>'answer option structure invalid'
                    ]);
                    exit;
                }

            if (trim((string)$answerOption['text'])==='' || trim((string)$answerOption['type'])==='')
                {
                    http_response_code(HTTP_STATUS_BAD_REQUEST);
                    echo json_encode([
                        'error'=>'Answer option text and type cannot be empty!!'
                    ]);

                    questionAddAuditLog('question_add_validation_failed',[
                        'reason'=>'answer option fields empty'
                    ]);
                    exit;
                }
        }

    /**
     5. RATE LIMIT
     */
    if (questionAddRateLimitCheck($uid)!==true)
        {
            http_response_code(HTTP_STATUS_TOO_MANY_REQUESTS);
            echo json_encode([
                'error'=>'Rate limit exceeded!!'
            ]);

            questionAddAuditLog('question_add_rate_limit_failed',[
                'uid'=>$uid
            ]);
            exit;
        }

    /**
     6. IDEMPOTENCY
     */
    if (questionAddIdempotencyCheck($uid)!==true)
        {
            http_response_code(HTTP_STATUS_CONFLICT);
            echo json_encode([
                'error'=>'Duplicate request detected!!'
            ]);

            questionAddAuditLog('question_add_idempotency_failed',[
                'uid'=>$uid
            ]);
            exit;
        }

    /**
     7. DELEGATE
     */
    $questionAddService=new QuestionAddService();
    $responseData=$questionAddService->questionAddService($questionText,$questionType,$answerOptions);

    /**
     8. RESPOND
     */
    http_response_code(HTTP_STATUS_OK);
    echo json_encode($responseData);

    /**
     9. AUDIT-LOG
     */
    questionAddAuditLog('question_add_success',[
        'uid'=>$uid,
        'questionId'=>$responseData['questionId'] ?? 0,
        'questionType'=>$responseData['questionType'] ?? ''
    ]);
}

try
{
    questionAddHandle();
}
catch (InvalidArgumentException $exception)
{
    http_response_code(HTTP_STATUS_BAD_REQUEST);
    echo json_encode([
        'error'=>'Invalid request input!!'
    ]);

    Logger::logWarn(
        'questionAddApi',
        'Invalid argument while adding question!!',
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
        'error'=>'Runtime failure while adding question!!'
    ]);

    Logger::logError(
        'questionAddApi',
        'Runtime failure while adding question!!',
        'QUESTION_ADD_RUNTIME_FAILURE',
        $exception,
        []
    );
}
catch (Throwable $exception)
{
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error'=>'Unexpected server error while adding question!!'
    ]);

    Logger::logFatal(
        'questionAddApi',
        'Unhandled exception while adding question!!',
        'QUESTION_ADD_UNHANDLED_EXCEPTION',
        $exception,
        []
    );
}
?>