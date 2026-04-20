<?php
declare(strict_types=1);

require_once __DIR__ . '/../../backend/answerOption.php';

class AnswerOptionMapper
{
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