<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/entity/gameConfig.php';

class GameConfigMapper
{
    //this will take 1 row and will return an object of game config, we will use this in the repository to get game config object from the db row
    public function getMappingSingleRow(array $row): GameConfig
    {
        //first take empty array for question id list that is allowed
        $questionIdListAllowed=[];

        //if there is a value set already then decode the json to question id list allowed array and true is given to get it in an associative array format
        if (isset($row['question_id_list_allowed_json'])) 
        {
            $questionIdListAllowed=json_decode($row['question_id_list_allowed_json'], true);

            if (!is_array($questionIdListAllowed)) //if there is no array on that location then we will get null and we want to convert that to empty array
            {
                $questionIdListAllowed=[];
            } 
            
            
            else //if there is an array then we want to make sure that all the values in that array are integers because they represent question ids
            {
                $questionIdListAllowed=array_map('intval', $questionIdListAllowed);
            }
        }

        //and then we will return the game config object with all the values from the row and the question id list allowed that we just processed
        return new GameConfig(
            (int)($row['id'] ?? 0),
            $row['game_config_name'] ?? '', //?? because if no value for that col then it will get null
            (int)($row['question_count_target'] ?? 0), //same for that
            $questionIdListAllowed,
            $row['secret_key'] ?? '',
            (bool)($row['is_active'] ?? false),
            $row['created_at'] ?? '',
            $row['updated_at'] ?? ''
        );
    }

    //this function will take an array of rows and return an array of game config objects by calling the getMappingSingleRow function for each row - though we will not need it just came from copying it from other file
    public function getMappingRows(array $rows): array
    {
        $data=[];
        foreach ($rows as $row) 
        {
            $data[]=$this->getMappingSingleRow($row);
        }
        return $data;
    }
}
