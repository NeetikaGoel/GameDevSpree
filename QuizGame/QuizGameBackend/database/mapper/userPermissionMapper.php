<?php
declare(strict_types=1);

//add user permission class
require_once __DIR__ . '/../../backend/entity/userPermission.php';

class UserPermissionMapper
{
    public function getMappingSingleRow(array $row):UserPermission
    {
        return new UserPermission(
            (int)($row['id'] ?? 0),
            (int)($row['uid'] ?? 0),
            $row['permission_group'] ?? '',
            $row['created_at'] ?? '',
            $row['updated_at'] ?? ''
        );
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