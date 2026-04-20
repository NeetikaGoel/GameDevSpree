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

    public function ormManage(string $sql,object $mapper): array
    {
        $rows=$this->dbManager->getAllRows($sql);
        return $mapper->getMappingRows($rows);
    }



    public function ormManageForOneRow(string $sql,string $types,array $params,object $mapper): ?array
    {
        $row = $this->dbManager->getAttemptIdRow($sql,$types,$params);

        if ($row===null) return null;

        return $mapper->getMappingSingleRow($row);
    }


    public function runQuery(string $sql,string $types,array $params): void
    {
        $this->dbManager->runQuery($sql,$types,$params);
    }

    public function insertQuery(string $sql,string $types,array $params): int
    {
        return $this->dbManager->insertAttemptIdRow($sql,$types,$params);
    }


}