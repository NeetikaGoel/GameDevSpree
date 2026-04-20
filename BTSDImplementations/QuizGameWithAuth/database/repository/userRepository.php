<?php
declare(strict_types=1);


//now what this will do???
//let's see


// create guest user
// create registered user
// get user by uid
// get user by user id
// get user by email
// later upgrade guest to registered


require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/../ormManager.php';
require_once __DIR__ . '/../query/userQuery.php';
require_once __DIR__ . '/../mapper/userMapper.php';

use Database\Query\UserQuery;

class UserRepository
{
    public function createGuestUser(string $userId):int
    {
        if ($userId==='')
            {
                return 0;
            }

        $userQuery=new UserQuery();
        // $userMapper=new UserMapper();
        $ormManager=new OrmManager();

        $loginType=USER_LOGIN_TYPE_GUEST;
        $email=null;
        $name=null;
        $password=null;
        $createdAt=date('Y-m-d H:i:s');
        $updatedAt=date('Y-m-d H:i:s');

        $sql=$userQuery->getInsertGuestUserSqlQuery();

        return $ormManager->insertQuery(
            $sql,
            'sssssss',
            [
                $userId,
                $loginType,
                $email,
                $name,
                $password,
                $createdAt,
                $updatedAt
            ]
        );
    }

    public function createRegisteredUser(string $userId,string $email,string $name,string $password):int
    {
        if ($userId==='' || $email==='' || $name==='' || $password==='')
            {
                return 0;
            }

        $userQuery=new UserQuery();
        $ormManager=new OrmManager();

        $loginType=USER_LOGIN_TYPE_REGISTERED;
        $createdAt=date('Y-m-d H:i:s');
        $updatedAt=date('Y-m-d H:i:s');

        $sql=$userQuery->getInsertRegisteredUserSqlQuery();

        return $ormManager->insertQuery(
            $sql,
            'sssssss',
            [
                $userId,
                $loginType,
                $email,
                $name,
                $password,
                $createdAt,
                $updatedAt
            ]
        );
    }

    public function getUserFromUid(int $uid):?User
    {
        if ($uid<=0)
            {
                return null;
            }

        $userQuery=new UserQuery();
        $userMapper=new UserMapper();
        $ormManager=new OrmManager();

        $sql=$userQuery->getSelectUserFromUidSqlQuery();

        return $ormManager->ormManageForOneRow(
            $sql,
            'i',
            [$uid],
            $userMapper
        );
    }

    public function getUserFromUserId(string $userId):?User
    {
        if ($userId==='')
            {
                return null;
            }

        $userQuery=new UserQuery();
        $userMapper=new UserMapper();
        $ormManager=new OrmManager();

        $sql=$userQuery->getSelectUserFromUserIdSqlQuery();

        return $ormManager->ormManageForOneRow(
            $sql,
            's',
            [$userId],
            $userMapper
        );
    }

    public function getUserFromEmail(string $email):?User
    {
        if ($email==='')
            {
                return null;
            }

        $userQuery=new UserQuery();
        $userMapper=new UserMapper();
        $ormManager=new OrmManager();

        $sql=$userQuery->getSelectUserFromEmailSqlQuery();

        return $ormManager->ormManageForOneRow(
            $sql,
            's',
            [$email],
            $userMapper
        );
    }

    public function updateGuestUserToRegistered(int $uid,string $email,string $name,string $password):void
    {
        if ($uid<=0 || $email==='' || $name==='' || $password==='')
            {
                return;
            }

        $userQuery=new UserQuery();
        $ormManager=new OrmManager();

        $loginType=USER_LOGIN_TYPE_REGISTERED;
        $updatedAt=date('Y-m-d H:i:s');

        $sql=$userQuery->getUpdateGuestUserToRegisteredSqlQuery();

        $ormManager->runQuery($sql,'sssssi',
            [
                $loginType,
                $email,
                $name,
                $password,
                $updatedAt,
                $uid
            ]
        );
    }
}
?>