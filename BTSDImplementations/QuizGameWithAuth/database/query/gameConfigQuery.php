<?php
declare(strict_types=1);

namespace Database\Query;

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
                question_id_list_allowed_json,
                secret_key,
                is_active,
                created_at,
                updated_at
            FROM game_configs
            WHERE game_config_name=?
            LIMIT 1
        '; //limit 1 coz what if we get multiple rows with same name so choose the 1st hehe
    }

    public function getSelectActiveGameConfigSqlQuery():string
    {
        return '
            SELECT
                id,
                game_config_name,
                question_count_target,
                question_id_list_allowed_json,
                secret_key,
                is_active,
                created_at,
                updated_at
            FROM game_configs
            WHERE is_active=TRUE
            ORDER BY id ASC
            LIMIT 1
        ';
    }

    public function getSelectAllGameConfigsSqlQuery():string
    {
        return '
            SELECT
                id,
                game_config_name,
                question_count_target,
                question_id_list_allowed_json,
                secret_key,
                is_active,
                created_at,
                updated_at
            FROM game_configs
            ORDER BY id ASC
        ';
    }

    public function getInsertGameConfigSqlQuery():string
    {
        return '
            INSERT INTO game_configs
            (
                game_config_name,
                question_count_target,
                question_id_list_allowed_json,
                secret_key,
                is_active
            )
            VALUES
            (?,?,?,?,?)
        ';
    }

    public function getUpdateGameConfigFromIdSqlQuery():string
    {
        return '
            UPDATE game_configs
            SET
                game_config_name=?,
                question_count_target=?,
                question_id_list_allowed_json=?,
                is_active=?
            WHERE id=?
        ';
    }

    public function getDeactivateAllGameConfigsSqlQuery():string
    {
        return '
            UPDATE game_configs
            SET
                is_active=FALSE
        ';
    }

    public function getActivateGameConfigFromIdSqlQuery():string
    {
        return '
            UPDATE game_configs
            SET
                is_active=TRUE
            WHERE id=?
        ';
    }
}
?>