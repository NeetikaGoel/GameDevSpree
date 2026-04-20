<?php
declare(strict_types=1);

class QuizAttemptMapper
{
    public function getMappingSingleRow(array $row):array
    {
        $questionIdOrder=json_decode($row['question_id_order_json'], true);

        if (!is_array($questionIdOrder))
            {
                $questionIdOrder=[];
            }

        return 
        [
            'id'=>(int)$row['id'],
            'scoreCurrent'=>(int)$row['score_current'],
            'questionsDone'=>(int)$row['questions_done'],
            'questionIdOrder'=>$questionIdOrder,
            'questionIdOrderIndexCurrent'=>(int)$row['question_id_order_index_current'],
            'questionIdCurrent'=>(int)$row['question_id_current'],
            'isComplete'=>(bool)$row['is_complete'],
            'createdAt'=>$row['created_at'],
            'updatedAt'=>$row['updated_at']
        ];
    }

    public function getMappingRows(array $rows):array
    {
        $data=[];
        foreach ($rows as $row)
            {
                $data[]=$this->getMappingSingleRow($row);
            }

        return $data;
    }

}




?>