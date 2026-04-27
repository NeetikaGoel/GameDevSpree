<?php

declare(strict_types=1);


require_once __DIR__ . '/../config.php';

class User
{
    private int $uid;
    private string $userId;
    private string $loginType;
    private ?string $email;
    private ?string $name;
    private ?string $passwordHash;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(int $uid,string $userId,string $loginType,?string $email,?string $name,?string $passwordHash,string $createdAt,string $updatedAt)
    {
        $this->uid=$uid;
        $this->userId=$userId;
        $this->loginType=$loginType;
        $this->email=$email;
        $this->name=$name;
        $this->passwordHash=$passwordHash;
        $this->createdAt=$createdAt;
        $this->updatedAt=$updatedAt;
    }

    //now put getters

    public function getUid():int
    {
        return $this->uid;
    }

    public function getUserId():string
    {
        return $this->userId;
    }

    public function getLoginType():string
    {
        return $this->loginType;
    }

    public function getEmail():?string
    {
        return $this->email;
    }

    public function getName():?string
    {
        return $this->name;
    }

    public function getPasswordHash():?string
    {
        return $this->passwordHash;
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