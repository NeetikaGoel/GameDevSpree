<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/entity/userProgressState.php';

class UserProgressStateMapper
{
    public function getMappingSingleRow(array $row): UserProgressState
    {
        //have to change ques id order from json to arr as well in this
        $questionIdOrder = [];

        if (isset($row['question_id_order_json'])) {
            $questionIdOrder = json_decode($row['question_id_order_json'], true);

            if (!is_array($questionIdOrder)) {
                $questionIdOrder = [];
            } else {
                $questionIdOrder = array_map('intval', $questionIdOrder); //have to convert all values to int type
            }
        }

        return new UserProgressState(
            (int)($row['id'] ?? 0),
            (int)($row['uid'] ?? 0),
            (int)($row['game_config_id'] ?? 0),
            (int)($row['score_current'] ?? 0),
            (int)($row['score_highest'] ?? 0),
            (int)($row['play_count'] ?? 0),
            $questionIdOrder,
            (int)($row['question_id_order_index_current'] ?? 0),
            $row['created_at'] ?? '',
            $row['updated_at'] ?? ''
        );
    }

    public function getMappingRows(array $rows): array
    {
        $data = [];

        foreach ($rows as $row) {
            $data[] = $this->getMappingSingleRow($row);
        }

        return $data;
    }
}
