<?php

declare(strict_types=1);

require_once __DIR__ . '/../ormManager.php';
require_once __DIR__ . '/../query/gameConfigQuery.php';
require_once __DIR__ . '/../mapper/gameConfigMapper.php';
require_once __DIR__ . '/../../backend/entity/gameConfig.php';

use Database\Query\GameConfigQuery;

class GameConfigRepository
{
    //this func we will write to get game config obj if we get its name as parameter and we will fetch it from db and convert to obj using mapper
    public function getGameConfigFromName(string $gameConfigName): ?GameConfig
    {
        //if no config name is provided then just return null
        if ($gameConfigName==='') {
            return null;
        }

        //but if name is provided then we will make a query to the database to fetch the game config with that name and then we will return the game config object if found otherwise we will return null
        $gameConfigQuery=new GameConfigQuery();
        $gameConfigMapper=new GameConfigMapper();
        $ormManager=new OrmManager();

        //we need query that will take its name as parameter and then orm can include it in query
        $sql=$gameConfigQuery->getSelectByGameConfigNameSqlQuery();

        $gameConfig=$ormManager->ormManageForOneRow($sql,'s',[$gameConfigName],$gameConfigMapper);
        //type is 's' coz its only 1 string parameter
        if (!$gameConfig instanceof GameConfig) {
            return null;
        }

        return $gameConfig;
    }

    public function getGameConfigFromId(int $gameConfigId): ?GameConfig
    {
        if ($gameConfigId<=0) {
            return null;
        }

        $gameConfigQuery=new GameConfigQuery();
        $gameConfigMapper=new GameConfigMapper();
        $ormManager=new OrmManager();

        $sql=$gameConfigQuery->getSelectGameConfigFromIdSqlQuery();

        $gameConfig=$ormManager->ormManageForOneRow($sql,'i',[$gameConfigId],$gameConfigMapper);

        if (!$gameConfig instanceof GameConfig) {
            return null;
        }

        return $gameConfig;
    }

    public function getActiveGameConfig(): ?GameConfig
    {
        $gameConfigQuery=new GameConfigQuery();
        $gameConfigMapper=new GameConfigMapper();
        $ormManager=new OrmManager();

        $sql=$gameConfigQuery->getSelectActiveGameConfigSqlQuery();

        $gameConfig=$ormManager->ormManageForOneRow($sql,'',[],$gameConfigMapper);

        if (!$gameConfig instanceof GameConfig) {
            return null;
        }

        return $gameConfig;
    }

    public function getAllGameConfigs(): array
    {
        $gameConfigQuery=new GameConfigQuery();
        $gameConfigMapper=new GameConfigMapper();
        $ormManager=new OrmManager();

        $sql=$gameConfigQuery->getSelectAllGameConfigsSqlQuery();

        return $ormManager->ormManage($sql,$gameConfigMapper);
    }

    public function getGameConfigsPageAfterId(int $cursor,int $limit): array
    {
        if ($cursor < 0 || $limit<=0) {
            return [];
        }

        $gameConfigQuery=new GameConfigQuery();
        $gameConfigMapper=new GameConfigMapper();
        $ormManager=new OrmManager();

        $sql=$gameConfigQuery->getSelectGameConfigsPageAfterIdSqlQuery();

        return $ormManager->ormManageWithParams(
            $sql,
            'ii',
            [
                $cursor,
                $limit
            ],
            $gameConfigMapper
        );
    }

    public function createGameConfig(string $gameConfigName,int $questionCountTarget,array $questionIdListAllowed,string $secretKey,bool $isActive): int
    {
        if ($gameConfigName==='' || $questionCountTarget<=0 || count($questionIdListAllowed)===0 || $secretKey==='') {
            return 0;
        }

        $questionIdListAllowedJson=json_encode(array_map('intval',$questionIdListAllowed));

        if ($questionIdListAllowedJson===false) {
            return 0;
        }

        $gameConfigQuery=new GameConfigQuery();
        $ormManager=new OrmManager();

        $sql=$gameConfigQuery->getInsertGameConfigSqlQuery();

        return $ormManager->insertQuery(
            $sql,
            'sissi',
            [
                $gameConfigName,
                $questionCountTarget,
                $questionIdListAllowedJson,
                $secretKey,
                $isActive?1:0
            ]
        );
    }

    public function updateGameConfigFromId(int $gameConfigId,string $gameConfigName,int $questionCountTarget,array $questionIdListAllowed,bool $isActive): void
    {
        if ($gameConfigId<=0 || $gameConfigName==='' || $questionCountTarget<=0 || count($questionIdListAllowed)===0) {
            return;
        }

        $questionIdListAllowedJson=json_encode(array_map('intval',$questionIdListAllowed));

        if ($questionIdListAllowedJson===false) {
            return;
        }

        $gameConfigQuery=new GameConfigQuery();
        $ormManager=new OrmManager();

        $sql=$gameConfigQuery->getUpdateGameConfigFromIdSqlQuery();

        $ormManager->runQuery(
            $sql,
            'sisii',
            [
                $gameConfigName,
                $questionCountTarget,
                $questionIdListAllowedJson,
                $isActive?1:0,
                $gameConfigId
            ]
        );
    }

    public function deactivateAllGameConfigs(): void
    {
        $gameConfigQuery=new GameConfigQuery();
        $ormManager=new OrmManager();

        $sql=$gameConfigQuery->getDeactivateAllGameConfigsSqlQuery();

        $ormManager->runQuery($sql,'',[]);
    }

    public function activateGameConfigFromId(int $gameConfigId): void
    {
        if ($gameConfigId<=0) {
            return;
        }

        $gameConfigQuery=new GameConfigQuery();
        $ormManager=new OrmManager();

        $sql=$gameConfigQuery->getActivateGameConfigFromIdSqlQuery();

        $ormManager->runQuery(
            $sql,
            'i',
            [$gameConfigId]
        );
    }
}
