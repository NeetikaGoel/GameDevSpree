<?php
declare(strict_types=1);

require_once __DIR__ . '/../ormManager.php';
require_once __DIR__ . '/../query/questionQuery.php';
require_once __DIR__ . '/../mapper/questionMapper.php';

use Database\Query\QuestionQuery;

class QuestionRepository
{

    //get all questions of db , will be too much to handle if million questions so just make new func that will only get ques acc to ques id list allowed for that game config
    public function getQuestions():array
    {
        $questionQuery=new QuestionQuery();
        $questionMapper=new QuestionMapper();
        $ormManager=new OrmManager();

        $sql=$questionQuery->getSqlQuery();

        return $ormManager->ormManage($sql,$questionMapper);
    }


    //here's the new func
    public function getQuestionsFromQuestionIdListAllowed(array $questionIdListAllowed,int $questionCountTarget):array
    {
        //if no ques id is there, return blank haha
        if (count($questionIdListAllowed)===0)
            {
                return [];
            }

        //if no ques count is there, return blank haha
        if ($questionCountTarget<=0)
            {
                return [];
            }

        //but if not that, then check all should be int
        //and count should be min of either the no of array size otherwise it can give error and what can, it will give error ofc
        $questionIdListAllowed=array_map('intval',$questionIdListAllowed);
        $questionCountTarget=min($questionCountTarget,count($questionIdListAllowed));

        //now want to get from db through orm
        $questionQuery=new QuestionQuery();
        $questionMapper=new QuestionMapper();
        $ormManager=new OrmManager();

        //get sql query which will take parameters and then it will be sent to orm
        $sql=$questionQuery->getSqlQueryFromQuestionIdListAllowed($questionIdListAllowed);
        //we also need types, which we can form as per no of ques
        //let me see how
        $types=str_repeat('i',count($questionIdListAllowed)) .str_repeat('i',count($questionIdListAllowed)) .'i';
        //first as per no of ques id so it will int, then again as per no of ques id for the same reason and then one more int for the question count target
        $params=array_merge($questionIdListAllowed,$questionIdListAllowed,[$questionCountTarget]);
        //params will have to be merged array of all 3 of which types we jsut defined
        return $ormManager->ormManageWithParams($sql,$types,$params,$questionMapper);
    }


    public function createQuestion(string $questionText,string $questionType):int
    {
        if ($questionText==='' || $questionType==='')
            {
                return 0;
            }

        $questionQuery=new QuestionQuery();
        $ormManager=new OrmManager();

        $sql=$questionQuery->getInsertQuestionSqlQuery();

        return $ormManager->insertQuery(
            $sql,
            'ss',
            [
                $questionText,
                $questionType
            ]
        );
    }


    //NEW FUNCTION FOR THE PURPOSE OF GETTING QUESTIONS IN PAGINATED FORM
    public function getQuestionPageAfterId(int $cursor, int $limit): array
    {
        if ($cursor < 0 || $limit <= 0) {
            return [];
        }

        $questionQuery=new QuestionQuery();
        $questionMapper=new QuestionMapper();
        $ormManager=new OrmManager();

        $sql=$questionQuery->getQuestionPageAfterIdSqlQuery();

        return $ormManager->ormManageWithParams(
            $sql,
            'ii',
            [
                $cursor,
                $limit
            ],
            $questionMapper
        );
    }
}
?>
