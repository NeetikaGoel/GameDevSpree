<?php
declare(strict_types=1);

namespace Database\Query;
//this file will save the queries that we need to get answer options from the db

class AnswerOptionQuery
{
    // sql query for getting all answer options
    public function getSqlQuery():string
    {
        return "SELECT id,text,type,question_id,is_correct FROM answer_options;";
    }

    //sql query for the answer options fetching but only for the question ids that are in the list
    public function getSqlQueryFromQuestionIdList(array $questionIdList):string
    {
        $placeholderCount=count($questionIdList);

        if ($placeholderCount===0) //which means no ques id was there in list so fetch nothing so give always false condition
            {
                return 'SELECT id,text,type,question_id,is_correct FROM answer_options WHERE 1=0';
            }

        //otherwise if some ques is there then implode means creating a string of ques marks with , so we can use that to fetch only those rows who have that ques id
        $placeholderList=implode(',',array_fill(0,$placeholderCount,'?')); //, is the separator and then filling array with ? acc to value of placeholder count

        return "SELECT id,text,type,question_id,is_correct FROM answer_options WHERE question_id IN ($placeholderList) ORDER BY question_id ASC;";
    }


    public function getInsertAnswerOptionSqlQuery():string
    {
        return '
            INSERT INTO answer_options
            (
                text,
                type,
                question_id,
                is_correct
            )
            VALUES
            (?, ?, ?, ?)
        ';
    }
}
?>