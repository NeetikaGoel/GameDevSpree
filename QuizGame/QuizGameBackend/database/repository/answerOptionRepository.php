<?php
declare(strict_types=1);

require_once __DIR__ . '/../ormManager.php';
require_once __DIR__ . '/../query/answerOptionQuery.php';
require_once __DIR__ . '/../mapper/answerOptionMapper.php';

use Database\Query\AnswerOptionQuery;

//this repo will act as bridge between backend and orm that will help us to fetch data from db through db manager
class AnswerOptionRepository
{
    //first get all answer options that r there in the db
    public function getAnswerOptions(): array
    {
        //take query first
        $answerOptionQuery=new AnswerOptionQuery();
        //then mapper
        $answerOptionMapper=new AnswerOptionMapper();
        //will need orm manager to manage the query and mapper and get data from db
        $ormManager=new OrmManager();

        //bring query from query obj
        $sql=$answerOptionQuery->getSqlQuery();

        //use orm to get data based on that sql query and then map it
        return $ormManager->ormManage($sql,$answerOptionMapper);
    }

    //now this func will only get ans options for ques ids that we need to show in quiz and not all
    public function getAnswerOptionsFromQuestionIdList(array $questionIdList):array
    {
        //no ques id is tehre,just return empty list
        if (count($questionIdList)===0)
            {
                return [];
            }

        //map all values of ques id in int format
        $questionIdList=array_map('intval',$questionIdList);

        //again we need query,mapper and orm manager to get data from db
        $answerOptionQuery=new AnswerOptionQuery();
        $answerOptionMapper=new AnswerOptionMapper();
        $ormManager=new OrmManager();

        //just use diff query which will take quesid list as placeholder and then return correct query
        $sql=$answerOptionQuery->getSqlQueryFromQuestionIdList($questionIdList);

        //use orm to include parameters to query and done
        return $ormManager->ormManageWithParams($sql,str_repeat('i',count($questionIdList)),$questionIdList,$answerOptionMapper);
    }


    public function createAnswerOption(string $text,string $type,int $questionId,bool $isCorrect):int
    {
        if ($text==='' || $type==='' || $questionId<=0)
            {
                return 0;
            }

        $answerOptionQuery=new AnswerOptionQuery();
        $ormManager=new OrmManager();

        $sql=$answerOptionQuery->getInsertAnswerOptionSqlQuery();

        return $ormManager->insertQuery(
            $sql,
            'ssii',
            [
                $text,
                $type,
                $questionId,
                $isCorrect ? 1 : 0
            ]
        );
    }
}
?>
