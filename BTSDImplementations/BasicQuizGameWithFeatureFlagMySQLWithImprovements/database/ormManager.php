<?php
declare(strict_types=1);

require_once __DIR__ . '/dbManager.php';

class OrmManager
{
    private DBManager $dbManager;

    public function __construct()
    {
        $this->dbManager=new DbManager();
    }

    public function ormManage(string $sql, object $mapper): array
    {
        $rows=$this->dbManager->dbManage($sql);
        return $mapper->getMapping($rows);
    }
}