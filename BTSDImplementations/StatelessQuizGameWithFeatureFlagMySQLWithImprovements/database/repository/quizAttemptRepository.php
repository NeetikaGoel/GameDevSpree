<?php
declare(strict_types=1);

require_once __DIR__ . '/../ormManager.php';
require_once __DIR__ . '/../query/quizAttemptQuery.php';
require_once __DIR__ . '/../mapper/quizAttemptMapper.php';

class QuizAttemptRepository
{

    public function createQuizAttempt(array $questionIdOrder):int
    {
        if (!isset($questionIdOrder[0]))
            {
                return 0;
            }

        $quizAttemptQuery=new QuizAttemptQuery();
        $ormManager=new OrmManager();

        $scoreCurrent=0;
        $questionsDone=0;
        $questionIdOrderJson=json_encode($questionIdOrder);
        $questionIdOrderIndexCurrent=0;
        $questionIdCurrent=(int)$questionIdOrder[0];
        $isComplete=false;
        $createdAt=date('Y-m-d H:i:s');
        $updatedAt=date('Y-m-d H:i:s');

        if ($questionIdOrderJson===false)
            {
                return 0;
            }

        $sql=$quizAttemptQuery->getInsertSqlQuery();

        return $ormManager->insertQuery(
            $sql,
            'iisiiiss',
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

    public function getQuizAttemptFromId(int $quizAttemptId):?array
    {
        $quizAttemptQuery=new QuizAttemptQuery();
        $quizAttemptMapper=new QuizAttemptMapper();
        $ormManager=new OrmManager();

        $sql=$quizAttemptQuery->getSelectByIdSqlQuery();

        return $ormManager->ormManageForOneRow(
            $sql,
            'i',
            [$quizAttemptId],
            $quizAttemptMapper
        );
    }

    public function updateQuizAttemptProgress(
        int $quizAttemptId,
        int $scoreCurrent,
        int $questionsDone,
        int $questionIdOrderIndexCurrent,
        int $questionIdCurrent
    ):void
    {
        $quizAttemptQuery=new QuizAttemptQuery();
        $ormManager=new OrmManager();

        $updatedAt=date('Y-m-d H:i:s');
        $sql=$quizAttemptQuery->getUpdateSqlQuery();

        $ormManager->runQuery(
            $sql,
            'iiiisi',
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

    public function markQuizAttemptComplete(
        int $quizAttemptId,
        int $scoreCurrent,
        int $questionsDone,
        int $questionIdOrderIndexCurrent,
        int $questionIdCurrent
    ):void
    {
        $quizAttemptQuery=new QuizAttemptQuery();
        $ormManager=new OrmManager();

        $isComplete=true;
        $updatedAt=date('Y-m-d H:i:s');
        $sql=$quizAttemptQuery->getMarkCompleteSqlQuery();

        $ormManager->runQuery(
            $sql,
            'iiiiisi',
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