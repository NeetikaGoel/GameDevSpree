<?php
declare(strict_types=1);

//so what does this relate to
//first of all we would need to insert a new user permission for this
//it will be insert query

//then also we would need to select it based on uid
//so it will be select query

// maybe only this, lets start now

//oh also we would need to update maybe at some point like guest becomes admin or vice versa

namespace Database\Query;

class UserPermissionQuery
{
    //insesrt query as usual
    public function getInsertUserPermissionSqlQuery():string
    {
        return '
            INSERT INTO user_permissions
            (
                uid,
                permission_group,
                created_at,
                updated_at
            )
            VALUES
            (?,?,?,?)
        ';
    }

    //select query
    public function getSelectUserPermissionFromUidSqlQuery():string
    {
        return '
            SELECT
                id,
                uid,
                permission_group,
                created_at,
                updated_at
            FROM user_permissions
            WHERE uid=?
        ';
    }

    //finally update query
    public function getUpdateUserPermissionSqlQuery():string
    {
        return '
            UPDATE user_permissions
            SET
                permission_group=?,
                updated_at=?
            WHERE uid=?
        ';
    }
}
?>