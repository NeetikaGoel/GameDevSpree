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
    public function createUserProgressState(int $uid,int $gameConfigId,array $questionIdOrder): int
    {
        if ($uid<=0) {
            return 0;
        }

        if ($gameConfigId<=0) {
            return 0;
        }

        if (!isset($questionIdOrder[0])) {
            return 0;
        }

        $userProgressStateQuery=new UserProgressStateQuery();
        $ormManager=new OrmManager();

        $scoreCurrent=0;
        $highestScore=0;
        $playCount=1;
        $questionIdOrderJson=json_encode($questionIdOrder); //might give error wait check needed
        $questionIdOrderIndexCurrent=0;
        $createdAt=date('Y-m-d H:i:s.u');
        $updatedAt=date('Y-m-d H:i:s.u');

        if ($questionIdOrderJson===false) {
            return 0;
        }

        $sql=$userProgressStateQuery->getInsertUserProgressStateSqlQuery();

        return $ormManager->insertQuery(
            $sql,
            'iiiiisiss',
            [
                $uid,
                $gameConfigId,
                $scoreCurrent,
                $highestScore,
                $playCount,
                $questionIdOrderJson,
                $questionIdOrderIndexCurrent,
                $createdAt,
                $updatedAt
            ]
        );
    }

    public function getUserProgressStateFromUidAndGameConfigId(int $uid,int $gameConfigId): ?UserProgressState
    {
        if ($uid<=0 || $gameConfigId<=0) {
            return null;
        }

        $userProgressStateQuery=new UserProgressStateQuery();
        $userProgressStateMapper=new UserProgressStateMapper();
        $ormManager=new OrmManager();

        $sql=$userProgressStateQuery->getSelectUserProgressStateFromUidAndGameConfigIdSqlQuery();

        return $ormManager->ormManageForOneRow(
            $sql,
            'ii',
            [$uid,$gameConfigId],
            $userProgressStateMapper
        );
    }

    public function getUserProgressStatesFromUid(int $uid): array
    {
        if ($uid<=0) {
            return [];
        }

        $userProgressStateQuery=new UserProgressStateQuery();
        $userProgressStateMapper=new UserProgressStateMapper();
        $ormManager=new OrmManager();

        $sql=$userProgressStateQuery->getSelectUserProgressStatesFromUidSqlQuery();

        return $ormManager->ormManageWithParams(
            $sql,
            'i',
            [$uid],
            $userProgressStateMapper
        );
    }

    public function updateUserProgressState(int $uid,int $gameConfigId,int $scoreCurrent,int $highestScore,int $playCount,array $questionIdOrder,int $questionIdOrderIndexCurrent): void
    {
        if ($uid<=0 || $gameConfigId<=0) {
            return;
        }

        $questionIdOrderJson=json_encode($questionIdOrder); //might give error again

        if ($questionIdOrderJson===false) {
            return;
        }

        $userProgressStateQuery=new UserProgressStateQuery();
        $ormManager=new OrmManager();

        $updatedAt=date('Y-m-d H:i:s.u');
        $sql=$userProgressStateQuery->getUpdateUserProgressStateSqlQuery();

        $ormManager->runQuery(
            $sql,
            'iiisisii',
            [
                $scoreCurrent,
                $highestScore,
                $playCount,
                $questionIdOrderJson,
                $questionIdOrderIndexCurrent,
                $updatedAt,
                $uid,
                $gameConfigId
            ]
        );
    }

    public function resetUserProgressState(int $uid,int $gameConfigId,int $highestScore,int $playCount,array $questionIdOrder): void
    {
        if ($uid<=0 || $gameConfigId<=0) {
            return;
        }

        $questionIdOrderJson=json_encode($questionIdOrder);

        if ($questionIdOrderJson===false) 
        {
            return;
        }

        $userProgressStateQuery=new UserProgressStateQuery();
        $ormManager=new OrmManager();

        $scoreCurrent=0;
        $questionIdOrderIndexCurrent=0;
        $updatedAt=date('Y-m-d H:i:s.u');
        $sql=$userProgressStateQuery->getResetUserProgressStateSqlQuery();

        $ormManager->runQuery(
            $sql,
            'iiisisii',
            [
                $scoreCurrent,
                $highestScore,
                $playCount,
                $questionIdOrderJson,
                $questionIdOrderIndexCurrent,
                $updatedAt,
                $uid,
                $gameConfigId
            ]
        );
    }

    public function deleteUserProgressStateFromUidAndGameConfigId(int $uid,int $gameConfigId): void
    {
        if ($uid<=0 || $gameConfigId<=0) 
        {
            return;
        }

        $userProgressStateQuery=new UserProgressStateQuery();
        $ormManager=new OrmManager();

        $sql=$userProgressStateQuery->getDeleteUserProgressStateFromUidAndGameConfigIdSqlQuery();

        $ormManager->runQuery(
            $sql,
            'ii',
            [$uid,$gameConfigId]
        );
    }
}
