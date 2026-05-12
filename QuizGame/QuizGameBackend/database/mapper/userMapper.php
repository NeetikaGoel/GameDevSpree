<?php
declare(strict_types=1);

//include user class first
require_once __DIR__ . '/../../backend/entity/user.php';

class UserMapper
{
    public function getMappingSingleRow(array $row):User
    {
        return new User(
            (int)($row['uid'] ?? 0),
            $row['user_id'] ?? '',
            $row['login_type'] ?? '',
            isset($row['email']) ? (string)$row['email']:null,
            isset($row['name']) ? (string)$row['name']:null,
            isset($row['password_hash']) ? (string)$row['password_hash']:null,
            $row['created_at'] ?? '',
            $row['updated_at'] ?? ''
        ); //?? represents is set so works
    }

    public function getMappingRows(array $rows):array
    {
        $data=[];

        foreach ($rows as $row)
            {
                $data[]=$this->getMappingSingleRow($row);
            }

        return $data;
    }
}
?>