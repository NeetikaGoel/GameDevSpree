<?php
declare(strict_types=1);

//so what will be the purpose of it first

// 1. creating guest user insert query
// 2. creating registered user again insert query
// 3. we would need to find user by:
// a. uid
// b. user_id
// c. email
// 4. also we might be lucky and finally guest user will upgrade to registered hehe


//now lets start!!!!

namespace Database\Query;

class UserQuery
{
    //first we need insert queries for both guest and admin
    public function getInsertGuestUserSqlQuery():string
    {
        return '
            INSERT INTO users
            (
                user_id,
                login_type,
                email,
                name,
                password_hash,
                created_at,
                updated_at
            )
            VALUES
            (?,?,?,?,?,?,?)
        ';
    }

    public function getInsertRegisteredUserSqlQuery():string
    {
        return '
            INSERT INTO users
            (
                user_id,
                login_type,
                email,
                name,
                password_hash,
                created_at,
                updated_at
            )
            VALUES
            (?,?,?,?,?,?,?)
        ';
    }

    //now select queries
    //it should be from either uid/userid or maybe from email when user logged in

    public function getSelectUserFromUidSqlQuery():string
    {
        return '
            SELECT
                uid,
                user_id,
                login_type,
                email,
                name,
                password_hash,
                created_at,
                updated_at
            FROM users
            WHERE uid=?
        ';
    }

    public function getSelectUserFromUserIdSqlQuery():string
    {
        return '
            SELECT
                uid,
                user_id,
                login_type,
                email,
                name,
                password_hash,
                created_at,
                updated_at
            FROM users
            WHERE user_id=?
        ';
    }

    public function getSelectUserFromEmailSqlQuery():string
    {
        return '
            SELECT
                uid,
                user_id,
                login_type,
                email,
                name,
                password_hash,
                created_at,
                updated_at
            FROM users
            WHERE email=?
        ';
    }

    //update query also in case guest logins!!!!!!!
    public function getUpdateGuestUserToRegisteredSqlQuery():string
    {
        return '
            UPDATE users
            SET
                login_type=?,
                email=?,
                name=?,
                password_hash=?,
                updated_at=?
            WHERE uid=?
        ';
    }
}
?>