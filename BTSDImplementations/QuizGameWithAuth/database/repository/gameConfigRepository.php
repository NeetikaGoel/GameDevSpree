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
    public function getGameConfigFromName(string $gameConfigName):?GameConfig
    {
        //if no config name is provided then just return null
        if ($gameConfigName==='')
            {
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
        if (!$gameConfig instanceof GameConfig)
            {
                return null;
            }

        return $gameConfig;
    }
}
?>