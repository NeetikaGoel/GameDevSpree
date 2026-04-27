<?php
declare(strict_types=1);

require_once __DIR__ . '/../ormManager.php';
require_once __DIR__ . '/../query/quizAttemptQuery.php';
require_once __DIR__ . '/../mapper/quizAttemptMapper.php';


use Database\Query\QuizAttemptQuery;

//this repo will serve as bridge btw db manage which will talk to db and server backend
class QuizAttemptRepository
{
    //this function will create a new quiz attempt in the database with the given question id order and return the id of the newly created quiz attempt if its not already there ofc
    public function createQuizAttempt(array $questionIdOrder):int
    {
        //if the question id order is empty then just return 0
        if (!isset($questionIdOrder[0]))
            {
                return 0;
            }


        //but if there is a question id order create new attempt hehe
        $quizAttemptQuery=new QuizAttemptQuery();
        $ormManager=new OrmManager(); 

        //when we create a new quiz attempt then initially the score will be 0 and questions done will be 0 and question id order index current will be 0 and question id current will be the first question id in the question id order and is complete will be false and created at and updated at will be current timestamp
        $scoreCurrent=0;
        $questionsDone=0;
        $questionIdOrderJson=json_encode($questionIdOrder); //encode it in json form,not be considered as arr
        $questionIdOrderIndexCurrent=0;
        $questionIdCurrent=$questionIdOrder[0]; //the first element of that array
        $isComplete=false;
        $createdAt=date('Y-m-d H:i:s'); //this format means year-month-day hour:minute:second 
        $updatedAt=date('Y-m-d H:i:s'); //same format hehe


        if ($questionIdOrderJson===false) //if some error occurs in creating correct json then retuen 0 already
            {
                return 0;
            }

        //now to insert the quiz attempt finally
        $sql=$quizAttemptQuery->getInsertSqlQuery();

        //this will return the id of the newly created quiz attempt
        return $ormManager->insertQuery($sql,'iisiiiss',
            [
                $scoreCurrent,
                $questionsDone,
                $questionIdOrderJson,
                $questionIdOrderIndexCurrent,
                $questionIdCurrent,
                $isComplete?1:0,
                $createdAt,
                $updatedAt
            ]
        );
    }

    //now we want to get that quiz attempt info if we have some id of it
    public function getQuizAttemptFromId(int $quizAttemptId):?array
    {
        $quizAttemptQuery=new QuizAttemptQuery();
        $quizAttemptMapper=new QuizAttemptMapper();
        $ormManager=new OrmManager();

        //select by id query we need now
        $sql=$quizAttemptQuery->getSelectByIdSqlQuery();

        return $ormManager->ormManageForOneRow($sql,'i',[$quizAttemptId],$quizAttemptMapper); //this will return an associative array with all the details of the quiz attempt if found otherwise it will return null 
    }

    // now updating query we need 
    public function updateQuizAttemptProgress(int $quizAttemptId,int $scoreCurrent,int $questionsDone,int $questionIdOrderIndexCurrent,int $questionIdCurrent):void
    {
        $quizAttemptQuery=new QuizAttemptQuery();
        $ormManager=new OrmManager();

        $updatedAt=date('Y-m-d H:i:s');
        $sql=$quizAttemptQuery->getUpdateSqlQuery();

        $ormManager->runQuery($sql,'iiiisi',
            [
                $scoreCurrent,
                $questionsDone,
                $questionIdOrderIndexCurrent,
                $questionIdCurrent,
                $updatedAt,
                $quizAttemptId
            ]
        );
    }

    //now we need to mark it complete so a new query add it in query file
    public function markQuizAttemptComplete(int $quizAttemptId,int $scoreCurrent,int $questionsDone,int $questionIdOrderIndexCurrent,int $questionIdCurrent):void
    {
        $quizAttemptQuery=new QuizAttemptQuery();
        $ormManager=new OrmManager();

        $isComplete=true;
        $updatedAt=date('Y-m-d H:i:s');
        $sql=$quizAttemptQuery->getMarkCompleteSqlQuery();

        $ormManager->runQuery($sql,'iiiiisi',
            [
                $scoreCurrent,
                $questionsDone,
                $questionIdOrderIndexCurrent,
                $questionIdCurrent,
                $isComplete?1:0,
                $updatedAt,
                $quizAttemptId
            ]
        );
    }

}