<?php

declare(strict_types=1);

//we need the repos because service layer will fetch all active configs and user progress data from db
require_once __DIR__ . '/../../database/repository/gameConfigRepository.php';
require_once __DIR__ . '/../../database/repository/userProgressStateRepository.php';

/** 
 * What it will do now???
1. Fetch all active configs
2. Fetch all saved progress rows of the user
3. Match progress row to config using game_config_id
4. For every active config, decide:
      NOT_STARTED
      IN_PROGRESS
      COMPLETED
5. Return a list that frontend will show
 */

//this service will prepare all user data to be shown for all active configs
class QuestionSetShowToUserService
{
    //this helper will take all progress rows of one user and make a map using gameConfigId as key
    //so later when we loop active configs, we can quickly see whether user has a row for that config or not
    private function getProgressMapFromRows(array $userProgressStates):array
    {
        $progressMap=[];

        foreach ($userProgressStates as $userProgressState) {
            //key will be config id and value will be full progress object
            $progressMap[$userProgressState->getGameConfigId()]=$userProgressState;
        }

        return $progressMap;
    }

    //this helper will decide whether a quiz is complete or not
    //we no longer store is_complete in db, so we derive it from current index and total question count
    private function getIsCompleteFromProgress(object $userProgressState):bool
    {
        $questionIdOrder=$userProgressState->getQuestionIdOrder(); //full question order array for that config attempt
        $questionCountTotal=count($questionIdOrder); //how many total questions are there
        $questionIdOrderIndexCurrent=$userProgressState->getQuestionIdOrderIndexCurrent(); //current pointer

        //if index has reached or crossed total count, it means quiz is complete
        if ($questionIdOrderIndexCurrent>=$questionCountTotal) {
            return true;
        }

        return false;
    }

    //main service function called by api file
    public function questionSetShowToUserService(int $uid):array
    {
        //basic input validation because service should not proceed with invalid uid
        if ($uid<=0) 
        {
            throw new InvalidArgumentException('Invalid uid for question set show to user!!');
        }

        //repo to fetch all active configs
        $gameConfigRepository=new GameConfigRepository();
        $userProgressStateRepository = new UserProgressStateRepository();
        //repo to fetch all saved progress rows of this user

        //get all active configs because only active configs should be shown on user main page
        $gameConfigsActive=$gameConfigRepository->getAllActiveGameConfigs();

        //get all progress rows for this uid so we can know for which config user has started/completed/in-progress data
        $userProgressStates=$userProgressStateRepository->getUserProgressStatesFromUid($uid);

        //convert user progress rows into a map keyed by gameConfigId for faster lookup
        $progressMap=$this->getProgressMapFromRows($userProgressStates);

        //final frontend-friendly list of configs hehe
        $gameConfigsData=[];

        //now loop through every active config and build user data for each one
        foreach ($gameConfigsActive as $gameConfig) {
            //we will take config id because that is the linking key between game_configs and user_progress_states
            $gameConfigId=$gameConfig->getId();

            //get question count from config row
            $questionCountTotal=$gameConfig->getQuestionCountTarget();

            //see if user has progress row for this active config
            $userProgressStateCurrent=$progressMap[$gameConfigId] ?? null;

            //default values assuming config is not started yet by user
            $status='NOT_STARTED';
            $playedAlready=false;
            $scoreCurrent=0;
            $scoreHighest=0;
            $playCount=0;
            $questionsDone=0;
            $isComplete=false;
            $showPlay=true;
            $showResume=false;
            $showPlayAgain=false;

            //if user has a progress row, then this config is either in progress/completed
            if ($userProgressStateCurrent!==null) {
                //read score values from saved progress row
                $scoreCurrent=$userProgressStateCurrent->getScoreCurrent();
                $scoreHighest=$userProgressStateCurrent->getScoreHighest();
                $playCount=$userProgressStateCurrent->getPlayCount();

                //questions done is now derived from current index
                $questionsDone=$userProgressStateCurrent->getQuestionIdOrderIndexCurrent();

                //derive completion state using helper
                $isComplete=$this->getIsCompleteFromProgress($userProgressStateCurrent);

                //if row exists, that means user has at least interacted with this config
                $playedAlready=($playCount>0);

                //if complete then status should be completed and replay option should be shown
                if ($isComplete===true) {
                    $status='COMPLETED';
                    $showPlay=false;
                    $showResume=false;
                    $showPlayAgain=true;
                } else {
                    //if row exists but not complete, then quiz is in progress
                    $status='IN_PROGRESS';
                    $showPlay=false;
                    $showResume=true;
                    $showPlayAgain=true; //allow replay/reset if user wants to restart same config from main page
                }
            }

            //build one config object exactly for frontend
            $gameConfigsData[]=[
                'gameConfigId'=>$gameConfigId, //unique config id used in later API calls
                'gameConfigName'=>$gameConfig->getGameConfigName(), //name user will see
                'questionCountTotal'=>$questionCountTotal, //total questions in that config
                'playedAlready'=>$playedAlready, //helps frontend show "played already" state
                'status'=>$status, //NOT_STARTED / IN_PROGRESS / COMPLETED
                'scoreCurrent'=>$scoreCurrent, //latest current score saved in row
                'scoreHighest'=>$scoreHighest, //best score for this user on this config
                'playCount'=>$playCount, //how many times this config was played by same user
                'questionsDone'=>$questionsDone, //derived progress count
                'isComplete'=>$isComplete, //derived completion flag returned for frontend convenience
                'showPlay'=>$showPlay, //frontend should show Play button
                'showResume'=>$showResume, //frontend should show Resume button
                'showPlayAgain'=>$showPlayAgain //frontend should show Play Again button
            ];
        }

        //final response returned to api file and then to frontend
        return [
            'uid'=>$uid, //current user whose dashboard this is
            'gameConfigs'=>$gameConfigsData //all active configs with user-specific state
        ];
    }
}
