<?php
declare(strict_types=1);

class QuizAttemptMapper
{
    //will take rows from the db and will return array of quiz attempt obj but we dont have quiz attempt class as question and answer option, so to not mmake it complex, we can return array of its data which we can use for the info we need in service class 
    public function getMappingSingleRow(array $row):array
    {
        $questionIdOrder=json_decode($row['question_id_order_json'], true); //we will decode the json to get the question id order as an array and true is for getting it as an associative array instead of object

        if (!is_array($questionIdOrder)) //no array then just return empty one
            {
                $questionIdOrder=[];
            }

        //return array with all values
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


    //this will take array of rows and return array of quiz attempt data arrays by calling the getMappingSingleRow function for each row- idts that we will need this but just copied it from other ones so we can delete it later if we don't need it
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