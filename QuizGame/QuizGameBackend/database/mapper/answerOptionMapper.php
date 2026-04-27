<?php
declare(strict_types=1);

require_once __DIR__ . '/../../backend/entity/answerOption.php';

class AnswerOptionMapper
{
    //same as question mapper but for answer option, just take rows and return array of answer option objects
    public function getMappingRows(array $rows):array
    {
        $data = [];
        foreach ($rows as $row)
            {
                $data[] = new AnswerOption((int)$row['id'],$row['text'],$row['type'],(int)$row['question_id'],
                    (bool)$row['is_correct']);
            }
        return $data;
    }
}




?>