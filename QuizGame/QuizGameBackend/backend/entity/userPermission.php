<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

class UserPermission
{
    private int $id;
    private int $uid;
    private string $permissionGroup;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(int $id,int $uid,string $permissionGroup,string $createdAt,string $updatedAt)
    {
        $this->id=$id;
        $this->uid=$uid;
        $this->permissionGroup=$permissionGroup;
        $this->createdAt=$createdAt;
        $this->updatedAt=$updatedAt;
    }

    //now time for getters
    public function getId():int
    {
        return $this->id;
    }

    public function getUid():int
    {
        return $this->uid;
    }

    public function getPermissionGroup():string
    {
        return $this->permissionGroup;
    }

    public function getCreatedAt():string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt():string
    {
        return $this->updatedAt;
    }
}
?>