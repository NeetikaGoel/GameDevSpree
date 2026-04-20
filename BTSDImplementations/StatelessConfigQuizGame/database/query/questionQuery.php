<?php
declare(strict_types=1);

class QuestionQuery
{
    public function getSqlQuery():string
    {
        return 'SELECT id,text,type FROM questions ORDER BY id ASC';
    }

    public function getSqlQueryFromQuestionIdListAllowed(array $questionIdListAllowed):string
    {
        //this will be sql query that will fetch only those rows who have particular question id
        $placeholderCount=count($questionIdListAllowed);

        if ($placeholderCount===0)
            {
                return 'SELECT id,text,type FROM questions WHERE 1=0'; //will be always false coz 1 is not equal to 0 at all so no rows will be fetched!!!!!
            }


            //if there are some question ids in the list then we will create a placeholder string like ?,?,? according to the number of question ids in the list and then we can use that placeholder string in the sql query to fetch only those rows who have particular ques id and also we will maintain the order of them in the list by using FIELD function in sql query
        $placeholderList=implode(',',array_fill(0,$placeholderCount,'?'));
        $placeholderOrderList=implode(',',array_fill(0,$placeholderCount,'?'));

        //so this query will fetch those rows whose id is in placeholder list and maintain its order acc to which they are in ques id order array and limit rows by question count target
        return "SELECT id,text,type FROM questions WHERE id IN ($placeholderList) ORDER BY FIELD(id,$placeholderOrderList) LIMIT ?";
    }
}
?>