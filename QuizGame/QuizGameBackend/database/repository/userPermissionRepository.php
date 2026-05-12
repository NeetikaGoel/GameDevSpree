<?php
declare(strict_types=1);

require_once __DIR__ . '/../../backend/entity/userPermission.php';

require_once __DIR__ . '/../ormManager.php';
require_once __DIR__ . '/../query/userPermissionQuery.php';
require_once __DIR__ . '/../mapper/userPermissionMapper.php';

use Database\Query\UserPermissionQuery;

//now lets see what to do here

// insert permission for a user
// get permission by uid
// update permission later if needed


class UserPermissionRepository
{
    public function createUserPermission(int $uid,string $permissionGroup):int
    {
        if ($uid<=0 || $permissionGroup==='')
            {
                return 0;
            }

        $userPermissionQuery=new UserPermissionQuery();
        $ormManager=new OrmManager();

        $createdAt=date('Y-m-d H:i:s');
        $updatedAt=date('Y-m-d H:i:s');

        $sql=$userPermissionQuery->getInsertUserPermissionSqlQuery();

        return $ormManager->insertQuery(
            $sql,
            'isss',
            [
                $uid,
                $permissionGroup,
                $createdAt,
                $updatedAt
            ]
        );
    }

    public function getUserPermissionFromUid(int $uid):?UserPermission
    {
        if ($uid<=0)
            {
                return null;
            }

        $userPermissionQuery=new UserPermissionQuery();
        $userPermissionMapper=new UserPermissionMapper();
        $ormManager=new OrmManager();

        $sql=$userPermissionQuery->getSelectUserPermissionFromUidSqlQuery();

        return $ormManager->ormManageForOneRow(
            $sql,
            'i',
            [$uid],
            $userPermissionMapper
        );
    }

    public function updateUserPermission(int $uid,string $permissionGroup):void
    {
        if ($uid<=0 || $permissionGroup==='')
            {
                return;
            }

        $userPermissionQuery=new UserPermissionQuery();
        $ormManager=new OrmManager();

        $updatedAt=date('Y-m-d H:i:s');
        $sql=$userPermissionQuery->getUpdateUserPermissionSqlQuery();

        $ormManager->runQuery(
            $sql,
            'ssi',
            [
                $permissionGroup,
                $updatedAt,
                $uid
            ]
        );
    }
}
?>