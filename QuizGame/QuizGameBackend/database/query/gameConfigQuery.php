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
        //though why we will get more with same name, it will only be 1 hehe
    }


    //to fetch that game config if we have id of it
    public function getSelectGameConfigFromIdSqlQuery():string
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
            WHERE id=?
            LIMIT 1
        ';
    }

    //to select the active game config from the table so where active=true
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

    public function getSelectAllActiveGameConfigsSqlQuery():string
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
        ';
    }

    //to get all game configs that r present in ascending order
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

    //it will fetch game config page using cursor id
    public function getSelectGameConfigsPageAfterIdSqlQuery():string
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
            WHERE id>?
            ORDER BY id ASC
            LIMIT ?
        ';
    }

    //this will insert game config that admin will add
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

    //this will get update query for game query if it is updated by admin yk
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

    //deactivate all the game configs if that toggle becomes true
    public function getDeactivateAllGameConfigsSqlQuery():string
    {
        return '
            UPDATE game_configs
            SET
                is_active=FALSE
        ';
    }

    //it will just active that game config which admin selects
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
