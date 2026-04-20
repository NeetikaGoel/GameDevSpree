<?php
declare(strict_types=1);

class GameConfigQuery
{
    //will just contain the sql query for fetching game config from db by its name so name will be placeholder
    public function getSelectByGameConfigNameSqlQuery():string
    {
        return '
            SELECT
                id,
                game_config_name,
                question_count_target,
                question_id_list_allowed_json
            FROM game_configs
            WHERE game_config_name=?
            LIMIT 1
        '; //limit 1 coz what if we get multiple rows with same name so choose the 1st hehe
    }
}
?>