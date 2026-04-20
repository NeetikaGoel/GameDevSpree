<?php
declare(strict_types=1);

require_once __DIR__ . '/../../backend/entity/question.php';

class QuestionMapper
{
    //just take 1 row and will return an object of question
    public function getMappingRows(array $rows):array
    {
        $data = [];
        foreach ($rows as $row)
            {
                $data[] = new Question((int)$row['id'],$row['text'],$row['type']);
            }

        return $data;
    }
}




?>