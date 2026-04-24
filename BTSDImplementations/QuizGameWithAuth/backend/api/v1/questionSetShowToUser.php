<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../params/questionSetShowToUserParams.php';
require_once __DIR__ . '/../../service/logging.php';
require_once __DIR__ . '/../../service/questionSetShowToUserService.php';

require_once __DIR__ . '/../../../database/repository/userRepository.php';
require_once __DIR__ . '/../../../database/repository/userPermissionRepository.php';

//this will tell the browser to treat the response as json data and not as html/text/anything else
header('Content-Type:application/json');

//AUTHENTICATE FUNCTION
function questionSetShowToUserAuthenticate(QuestionSetShowToUserParams $params): bool
{
    $uid = $params->getUid();

    if ($uid === null || $uid <= 0) {
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
function questionSetShowToUserAuthorize(QuestionSetShowToUserParams $params): bool
{
    $uid = $params->getUid();

    if ($uid === null || $uid <= 0) {
        return false;
    }

    $userPermissionRepository = new UserPermissionRepository();
    $userPermissionCurrent = $userPermissionRepository->getUserPermissionFromUid($uid);

    if ($userPermissionCurrent === null) {
        return false;
    }

    $permissionGroup = $userPermissionCurrent->getPermissionGroup();

    //allow all three because dashboard page is for any playable user type
    if ($permissionGroup !== USER_PERMISSION_GROUP_GUEST && $permissionGroup !== USER_PERMISSION_GROUP_ADMIN && $permissionGroup !== USER_PERMISSION_GROUP_USER) {
        return false;
    }

    return true;
}

//RATE LIMIT FUNCTION
function questionSetShowToUserRateLimitCheck(QuestionSetShowToUserParams $params): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//IDEMPOTENCY FUNCTION
function questionSetShowToUserIdempotencyCheck(QuestionSetShowToUserParams $params): bool
{
    return true; //PLACEHOLDER FOR NOW
}

//AUDIT LOG FUNCTION
function questionSetShowToUserAuditLog(string $action, array $context): void
{
    Logger::logInfo('questionSetShowToUserApi', $action, $context);
}

//MAIN BOUNDARY FUNCTION
function questionSetShowToUserHandle(): void
{

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(HTTP_STATUS_METHOD_NOT_ALLOWED);
        echo json_encode([
            'error' => 'Invalid request method, GET required!!'
        ]);

        questionSetShowToUserAuditLog('question_set_show_to_user_method_failed', [
            'method' => $_SERVER['REQUEST_METHOD'] ?? null
        ]);
        exit;
    }


    /**
     1. AUTHENTICATE
     */
    $uid = null;

    if (isset($_GET['uid'])) {
        $uidRaw = trim((string)$_GET['uid']);

        if ($uidRaw !== '' && is_numeric($uidRaw)) {
            $uid = (int)$uidRaw;
        }
    }

    $questionSetShowToUserParams = new QuestionSetShowToUserParams($uid);

    if (questionSetShowToUserAuthenticate($questionSetShowToUserParams) !== true) {
        http_response_code(HTTP_STATUS_UNAUTHORIZED);
        echo json_encode([
            'error' => 'Authentication failed!!'
        ]);

        questionSetShowToUserAuditLog('question_set_show_to_user_authentication_failed', [
            'uid' => $uid
        ]);
        exit;
    }

    /**
     2. AUTHORIZE
     */
    if (questionSetShowToUserAuthorize($questionSetShowToUserParams) !== true) {
        http_response_code(HTTP_STATUS_FORBIDDEN);
        echo json_encode([
            'error' => 'Authorization failed!!'
        ]);

        questionSetShowToUserAuditLog('question_set_show_to_user_authorization_failed', [
            'uid' => $uid
        ]);
        exit;
    }

    /**
     3. VALIDATE
     4. SANITIZE
     */
    $uid = null;

    if (isset($_GET['uid'])) {
        $uidRaw = trim((string)$_GET['uid']);

        if ($uidRaw === '') {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error' => 'Invalid uid,it cannot be empty!!'
            ]);

            questionSetShowToUserAuditLog('question_set_show_to_user_validation_failed', [
                'reason' => 'uid empty'
            ]);
            exit;
        }

        if (!is_numeric($uidRaw)) {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error' => 'Invalid uid,it should be numeric!!'
            ]);

            questionSetShowToUserAuditLog('question_set_show_to_user_validation_failed', [
                'reason' => 'uid non numeric'
            ]);
            exit;
        }

        $uid = (int)$uidRaw;

        if ($uid <= 0) {
            http_response_code(HTTP_STATUS_BAD_REQUEST);
            echo json_encode([
                'error' => 'Invalid uid,it should be positive number!!'
            ]);

            questionSetShowToUserAuditLog('question_set_show_to_user_validation_failed', [
                'reason' => 'uid non positive',
                'uid' => $uid
            ]);
            exit;
        }
    } else {
        http_response_code(HTTP_STATUS_BAD_REQUEST);
        echo json_encode([
            'error' => 'Invalid uid,it is required!!'
        ]);

        questionSetShowToUserAuditLog('question_set_show_to_user_validation_failed', [
            'reason' => 'uid missing'
        ]);
        exit;
    }

    $questionSetShowToUserParams = new QuestionSetShowToUserParams($uid);

    /**
     5. RATE LIMIT
     */
    if (questionSetShowToUserRateLimitCheck($questionSetShowToUserParams) !== true) {
        http_response_code(HTTP_STATUS_TOO_MANY_REQUESTS);
        echo json_encode([
            'error' => 'Rate limit exceeded!!'
        ]);

        questionSetShowToUserAuditLog('question_set_show_to_user_rate_limit_failed', [
            'uid' => $uid
        ]);
        exit;
    }

    /**
     6. IDEMPOTENCY
     */
    if (questionSetShowToUserIdempotencyCheck($questionSetShowToUserParams) !== true) {
        http_response_code(HTTP_STATUS_CONFLICT);
        echo json_encode([
            'error' => 'Duplicate request detected!!'
        ]);

        questionSetShowToUserAuditLog('question_set_show_to_user_idempotency_failed', [
            'uid' => $uid
        ]);
        exit;
    }

    /**
     7. DELEGATE
     NOW HERE WE WILL NOT WRITE BUSINESS LOGIC
     WE WILL CALL SERVICE CLASS FOR THAT
     */
    $questionSetShowToUserService = new QuestionSetShowToUserService();
    $responseData = $questionSetShowToUserService->questionSetShowToUserService($uid);

    /**
     8. RESPOND
     */
    http_response_code(HTTP_STATUS_OK);
    echo json_encode($responseData);

    /**
     9. AUDIT-LOG
     */
    questionSetShowToUserAuditLog('question_set_show_to_user_success', [
        'uid' => $responseData['uid'] ?? $uid,
        //service should now return activeGameConfigs for user dashboard, so log that count
        'activeConfigCount' => isset($responseData['activeGameConfigs']) && is_array($responseData['activeGameConfigs']) ? count($responseData['activeGameConfigs']) : 0
    ]);
}

try {
    questionSetShowToUserHandle();
} catch (InvalidArgumentException $exception) {
    http_response_code(HTTP_STATUS_BAD_REQUEST);
    echo json_encode([
        'error' => 'Invalid request input!!'
    ]);

    Logger::logWarn(
        'questionSetShowToUserApi',
        'Invalid argument while showing question sets to user!!',
        'INVALID_ARGUMENT',
        [
            'errorMessage' => $exception->getMessage()
        ]
    );
} catch (RuntimeException $exception) {
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error' => 'Runtime failure while showing question sets to user!!'
    ]);

    Logger::logError('questionSetShowToUserApi', 'Runtime failure while showing question sets to user!!', 'QUESTION_SET_SHOW_TO_USER_RUNTIME_FAILURE', $exception, []);
} catch (Throwable $exception) {
    http_response_code(HTTP_STATUS_INTERNAL_SERVER_ERROR);
    echo json_encode([
        'error' => 'Unexpected server error while showing question sets to user!!'
    ]);

    Logger::logFatal('questionSetShowToUserApi', 'Unhandled exception while showing question sets to user!!', 'QUESTION_SET_SHOW_TO_USER_UNHANDLED_EXCEPTION', $exception, []);
}
