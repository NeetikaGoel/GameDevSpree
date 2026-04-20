<?php
declare(strict_types=1);

require_once __DIR__ . '/../../backend/question.php';

class QuestionMapper
{
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