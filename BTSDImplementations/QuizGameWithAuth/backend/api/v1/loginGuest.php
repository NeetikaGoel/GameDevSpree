<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../service/logging.php';
require_once __DIR__ . '/../service/loginGuestService.php';

header('Content-Type:application/json');

//AUTHENTICATE FUNCTION
function loginGuestAuthenticate():bool
{
    return true; //WILL BE TRUE ALWAYS SINCE LANDING PAGE, NO AUTHENTICATION NEEDED HERE
}

//AUTHORIZE FUNCTION
function loginGuestAuthorize():bool
{
    return true; //WILL BE TRUE ALWAYS SINCE LANDING PAGE, NO AUTHORIZATION NEEDED HERE
}

//RATE LIMIT FUNCTION
function loginGuestRateLimitCheck():bool
{
    return true; //PLACEHOLDER FOR NOW
}

//IDEMPOTENCY FUNCTION
function loginGuestIdempotencyCheck():bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUDIT LOG FUNCTION
function loginGuestAuditLog(string $action,array $context):void
{
    Logger::logInfo('loginGuestApi',$action,$context);
}

//MAIN BOUNDARY FUNCTION
function loginGuestHandle():void
{
    /**
     1. AUTHENTICATE
     */
    if (loginGuestAuthenticate()!==true)
        {
            http_response_code(HTTP_STATUS_UNAUTHORIZED);
            echo json_encode([
                'error'=>'Authentication failed!!'
            ]);

            loginGuestAuditLog('login_guest_authentication_failed',[]);
            exit;
        }

    /**
     2. AUTHORIZE
     */
    if (loginGuestAuthorize()!==true)
        {
            http_response_code(HTTP_STATUS_FORBIDDEN);
            echo json_encode([
                'error'=>'Authorization failed!!'
            ]);

            loginGuestAuditLog('login_guest_authorization_failed',[]);
            exit;
        }

    /**
     3. VALIDATE
     4. SANITIZE
     */
    //NO INPUT REQUIRED FOR GUEST LOGIN RIGHT NOW

    if ($_SERVER['REQUEST_METHOD']!=='POST')
        {
            http_response_code(HTTP_STATUS_METHOD_NOT_ALLOWED);
            echo json_encode([
                'error'=>'Only POST method is allowed!!'
            ]);

            loginGuestAuditLog('login_guest_validation_failed',[
                'reason'=>'invalid request method',
                'requestMethod'=>$_SERVER['REQUEST_METHOD'] ?? ''
            ]);
            exit;
        }

    /**
     5. RATE LIMIT
     */
    if (loginGuestRateLimitCheck()!==true)
        {
            http_response_code(HTTP_STATUS_TOO_MANY_REQUESTS);
            echo json_encode([
                'error'=>'Rate limit exceeded!!'
            ]);

            loginGuestAuditLog('login_guest_rate_limit_failed',[]);
            exit;
        }

    /**
     6. IDEMPOTENCY
     */
    if (loginGuestIdempotencyCheck()!==true)
        {
            http_response_code(HTTP_STATUS_CONFLICT);
            echo json_encode([
                'error'=>'Duplicate request detected!!'
            ]);

            loginGuestAuditLog('login_guest_idempotency_failed',[]);
            exit;
        }

    /**
     7. DELEGATE
     */
    $loginGuestService=new LoginGuestService();
    $responseData=$loginGuestService->loginGuestService();

    /**
     8. RESPOND
     */
    http_response_code(HTTP_STATUS_OK);
    echo json_encode($responseData);

    /**
     9. AUDIT-LOG
     */
    loginGuestAuditLog('login_guest_success',[
        'uid'=>$responseData['uid'] ?? 0,
        'userId'=>$responseData['userId'] ?? '',
        'loginType'=>$responseData['loginType'] ?? '',
        'permissionGroup'=>$responseData['permissionGroup'] ?? ''
    ]);
}

try
{
    loginGuestHandle();
}


catch (InvalidArgumentException $exception)
{
    http_response_code(HTTP_STATUS_BAD_REQUEST);
    echo json_encode([
        'error'=>'Invalid request input!!'
    ]);

    Logger::logWarn(
        'loginGuestApi',
        'Invalid argument while logging in guest!!',
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
        'error'=>'Runtime failure while logging in guest!!'
    ]);

    Logger::logError(
        'loginGuestApi',
        'Runtime failure while logging in guest!!',
        'LOGIN_GUEST_RUNTIME_FAILURE',
        $exception,
        []
    );
}
catch (Throwable $exception)
{
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error'=>'Unexpected server error while logging in guest!!'
    ]);

    Logger::logFatal(
        'loginGuestApi',
        'Unhandled exception while logging in guest!!',
        'LOGIN_GUEST_UNHANDLED_EXCEPTION',
        $exception,
        []
    );
}
?>