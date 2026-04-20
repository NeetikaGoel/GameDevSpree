<?php
declare(strict_types=1);

require_once __DIR__ . '/../../backend/entity/userProgressState.php';

require_once __DIR__ . '/../ormManager.php';
require_once __DIR__ . '/../query/userProgressStateQuery.php';
require_once __DIR__ . '/../mapper/userProgressStateMapper.php';


use Database\Query\UserProgressStateQuery;

//now what this will do

// create progress state for user
// will bring progress state by uid
// update progress state as per progress
// mark complete if done
// delete progress hehe


//lets start
class UserProgressStateRepository
{
    public function createUserProgressState(int $uid,array $questionIdOrder):int
    {
        if ($uid<=0)
            {
                return 0;
            }

        if (!isset($questionIdOrder[0]))
            {
                return 0;
            }

        $userProgressStateQuery=new UserProgressStateQuery();
        $ormManager=new OrmManager();

        $scoreCurrent=0;
        $questionsDone=0;
        $questionIdOrderJson=json_encode($questionIdOrder); //might give error wait check needed
        $questionIdOrderIndexCurrent=0;
        $questionIdCurrent=(int)$questionIdOrder[0];
        $isComplete=false;
        $createdAt=date('Y-m-d H:i:s');
        $updatedAt=date('Y-m-d H:i:s');

        if ($questionIdOrderJson===false)
            {
                return 0;
            }

        $sql=$userProgressStateQuery->getInsertUserProgressStateSqlQuery();

        return $ormManager->insertQuery(
            $sql,
            'iiisiiiss',
            [
                $uid,
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

    public function getUserProgressStateFromUid(int $uid):?UserProgressState
    {
        if ($uid<=0)
            {
                return null;
            }

        $userProgressStateQuery=new UserProgressStateQuery();
        $userProgressStateMapper=new UserProgressStateMapper();
        $ormManager=new OrmManager();

        $sql=$userProgressStateQuery->getSelectUserProgressStateFromUidSqlQuery();

        return $ormManager->ormManageForOneRow(
            $sql,
            'i',
            [$uid],
            $userProgressStateMapper
        );
    }

    public function updateUserProgressState(int $uid,int $scoreCurrent,int $questionsDone,array $questionIdOrder,int $questionIdOrderIndexCurrent,int $questionIdCurrent,bool $isComplete):void
    {
        if ($uid<=0)
            {
                return;
            }

        $questionIdOrderJson=json_encode($questionIdOrder); //might give error again

        if ($questionIdOrderJson===false)
            {
                return;
            }

        $userProgressStateQuery=new UserProgressStateQuery();
        $ormManager=new OrmManager();

        $updatedAt=date('Y-m-d H:i:s');
        $sql=$userProgressStateQuery->getUpdateUserProgressStateSqlQuery();

        $ormManager->runQuery(
            $sql,
            'iisiiisi',
            [
                $scoreCurrent,
                $questionsDone,
                $questionIdOrderJson,
                $questionIdOrderIndexCurrent,
                $questionIdCurrent,
                $isComplete?1:0,
                $updatedAt,
                $uid
            ]
        );
    }

    public function markUserProgressStateComplete(int $uid,int $scoreCurrent,int $questionsDone,array $questionIdOrder,int $questionIdOrderIndexCurrent,int $questionIdCurrent):void
    {
        if ($uid<=0)
            {
                return;
            }

        $questionIdOrderJson=json_encode($questionIdOrder);

        if ($questionIdOrderJson===false)
            {
                return;
            }

        $userProgressStateQuery=new UserProgressStateQuery();
        $ormManager=new OrmManager();

        //now mark it true 
        $isComplete=true; 
        $updatedAt=date('Y-m-d H:i:s');
        $sql=$userProgressStateQuery->getMarkUserProgressStateCompleteSqlQuery();

        $ormManager->runQuery(
            $sql,
            'iisiiisi',
            [
                $scoreCurrent,
                $questionsDone,
                $questionIdOrderJson,
                $questionIdOrderIndexCurrent,
                $questionIdCurrent,
                $isComplete?1:0,
                $updatedAt,
                $uid
            ]
        );
    }

    public function deleteUserProgressStateFromUid(int $uid):void
    {
        if ($uid<=0)
            {
                return;
            }

        $userProgressStateQuery=new UserProgressStateQuery();
        $ormManager=new OrmManager();

        $sql=$userProgressStateQuery->getDeleteUserProgressStateFromUidSqlQuery();

        $ormManager->runQuery(
            $sql,
            'i',
            [$uid]
        );
    }
}
?>