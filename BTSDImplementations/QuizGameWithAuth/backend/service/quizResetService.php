<?php

declare(strict_types=1);

//we need config repo to fetch selected config and its question list
require_once __DIR__ . '/../../database/repository/gameConfigRepository.php';
require_once __DIR__ . '/../../database/repository/userProgressStateRepository.php';
//we need progress repo to fetch existing progress row and reset/update it

/** 
 * 
 * WHAT THIS WILL DO???
 * 
1. Validate uid and config id
2. Load config
3. Load existing progress row for that user+config
4. Read latest config question list
5. Preserve score_highest
6. Increment play_count
7. Reset same row back to fresh state
8. Return success payload

 */


//this service will reset one user's one config progress row while preserving highest score
class QuizResetService
{
    //this function will make sure question id list taken from config is clean integer array
    private function getSanitizedQuestionIdOrder(array $questionIdListAllowed):array
    {
        $questionIdOrder=[];

        foreach ($questionIdListAllowed as $questionId) 
        {
            $questionIdInt=(int)$questionId; //convert all ids to int safely

            if ($questionIdInt>0) 
            {
                $questionIdOrder[]=$questionIdInt; //keep only positive ids
            }
        }

        //remove duplicates just in case same question id somehow appears more than once in config list
        $questionIdOrder=array_values(array_unique($questionIdOrder));

        return $questionIdOrder;
    }

    //main service function called by api boundary
    public function quizResetService(int $uid,int $gameConfigId):array
    {
        //service should reject invalid uid immediately
        if ($uid<=0) 
        {
            throw new InvalidArgumentException('Invalid uid for quiz reset!!');
        }

        //service should reject invalid config id too
        if ($gameConfigId<=0) 
        {
            throw new InvalidArgumentException('Invalid gameConfigId for quiz reset!!');
        }

        //repo for config fetch
        $gameConfigRepository=new GameConfigRepository();

        //repo for progress fetch/update
        $userProgressStateRepository=new UserProgressStateRepository();

        //load config because reset must use latest question set definition from config
        $gameConfigCurrent=$gameConfigRepository->getGameConfigFromId($gameConfigId);

        //if config not found,reset cannot proceed
        if ($gameConfigCurrent===null)
        {
            throw new RuntimeException('Game config not found while resetting quiz!!');
        }

        //load existing progress row for that uid+game config id
        $userProgressStateCurrent=$userProgressStateRepository->getUserProgressStateFromUidAndGameConfigId($uid,$gameConfigId);

        //if no row exists,reset does not make sense because there is nothing to reset
        if ($userProgressStateCurrent===null) {
            throw new RuntimeException('User progress state not found for quiz reset!!');
        }

        //take latest allowed question list from config so replay is based on latest config contents
        $questionIdListAllowed=$gameConfigCurrent->getQuestionIdListAllowed();

        //sanitize it into a clean order array
        $questionIdOrder=$this->getSanitizedQuestionIdOrder($questionIdListAllowed);

        //if config question list becomes empty,reset should fail because user cannot play empty quiz
        if (count($questionIdOrder)===0) {
            throw new RuntimeException('Question list is empty while resetting quiz!!');
        }

        //preserve highest score because replay should not erase best historical score
        $scoreHighest=$userProgressStateCurrent->getScoreHighest();

        //increase play count because same user is starting one more attempt on same config
        $playCount=$userProgressStateCurrent->getPlayCount()+1;

        //now reset same row
        //this will set:
        //score_current=0
        //question_id_order_index_current=0
        //question_id_order_json=fresh config order
        //score_highest preserved
        //play_count incremented
        $userProgressStateRepository->resetUserProgressState(
            $uid,
            $gameConfigId,
            $scoreHighest,
            $playCount,
            $questionIdOrder
        );

        //return frontend-friendly response
        return [
            'uid'=>$uid,//user whose quiz got reset
            'gameConfigId'=>$gameConfigId,//which config was reset
            'gameConfigName'=>$gameConfigCurrent->getGameConfigName(),//config name for UI display if needed
            'scoreCurrent'=>0,//fresh run starts from zero current score
            'scoreHighest'=>$scoreHighest,//best score preserved
            'playCount'=>$playCount,//new total attempts count after reset
            'questionCountTotal'=>count($questionIdOrder),//how many questions are there in reset run
            'isReset'=>true //frontend can use this to confirm reset success
        ];
    }
}
