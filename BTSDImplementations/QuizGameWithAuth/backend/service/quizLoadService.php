<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../entity/question.php';
require_once __DIR__ . '/../entity/answerOption.php';
require_once __DIR__ . '/../entity/gameConfig.php';
require_once __DIR__ . '/../entity/quiz.php';
require_once __DIR__ . '/../entity/featureFlag.php';

require_once __DIR__ . '/logging.php';
require_once __DIR__ . '/../../database/repository/userProgressStateRepository.php';
require_once __DIR__ . '/../../database/repository/gameConfigRepository.php';

//ALL BUSINESS LOGIC OF QUIZ LOAD FILE HERE 


//now what all it will do::::
// 1. create new quiz attempt if needed
// 2. get quiz attempt from DB
// 3. check if attempt is complete
// 4. read current question order
// 5. get current question
// 6. get answer options
// 7. build response data

//business logic of quizLoad api here
class QuizLoadService
{
    //this helper will decide if quiz is complete or not using derived logic only
    //we no longer store is_complete in db
    private function getIsQuizComplete(array $questionIdOrder, int $questionIdOrderIndexCurrent): bool
    {
        //total questions in this config attempt
        $questionCountTotal = count($questionIdOrder);

        //if current index has reached total count, quiz is complete
        if ($questionIdOrderIndexCurrent >= $questionCountTotal) {
            return true;
        }

        //otherwise quiz is still in progress
        return false;
    }

    //this helper will clean question id order before saving/using it
    private function getSanitizedQuestionIdOrder(array $questionIdOrder): array
    {
        $questionIdOrderSanitized = [];

        //loop all ids and keep only positive integers
        foreach ($questionIdOrder as $questionIdTemp) {
            $questionIdTempInt = (int)$questionIdTemp;

            if ($questionIdTempInt > 0) {
                $questionIdOrderSanitized[] = $questionIdTempInt;
            }
        }

        //remove duplicate ids and reset array indexes
        return array_values(array_unique($questionIdOrderSanitized));
    }

    //take quiz attempt id and return data that we need to show the quiz question and answer options and also quiz attempt id for future calls and also if quiz is done or not and if done then link to result page
    public function quizLoadService(int $uid, int $gameConfigId): array
    {
        //uid must be valid because everything is user specific
        if ($uid <= 0) {
            throw new InvalidArgumentException('Invalid uid for quiz load!!');
        }

        //game config id must also be valid because quiz is now per config
        if ($gameConfigId <= 0) {
            throw new InvalidArgumentException('Invalid gameConfigId for quiz load!!');
        }

        //initialize progress state repo to interact with user progress state table in db through orm
        $userProgressStateRepository = new UserProgressStateRepository();

        //initialize config repo because we now load questions from selected config and not from a single global default flow
        $gameConfigRepository = new GameConfigRepository();

        //load the selected config row first
        $gameConfigCurrent = $gameConfigRepository->getGameConfigFromId($gameConfigId);

        //if config does not exist we cannot continue
        if ($gameConfigCurrent === null) {
            throw new RuntimeException('Game config not found while loading quiz!!');
        }

        //only active configs should be playable from main user flow
        if ($gameConfigCurrent->getIsActive() !== true) {
            throw new RuntimeException('Selected game config is not active for quiz load!!');
        }

        //now fetch user progress row using uid+gameConfigId because one row exists per user per config
        $userProgressState = $userProgressStateRepository->getUserProgressStateFromUidAndGameConfigId($uid, $gameConfigId);

        //if no row exists for this user+config then create fresh row for first play
        if ($userProgressState === null) {
            //build question order from this specific config
            $questionIdOrder = $this->getQuestionIdOrderFromConfig($gameConfigCurrent);

            //if order becomes empty then quiz cannot start
            if (count($questionIdOrder) === 0) {
                throw new RuntimeException('Question id order could not be created!!');
            }

            //create fresh row for this exact user+config pair
            $userProgressStateId = $userProgressStateRepository->createUserProgressState($uid, $gameConfigId, $questionIdOrder);

            //if insert failed then stop
            if ($userProgressStateId <= 0) {
                throw new RuntimeException('User progress state creation failed!!');
            }

            //load the row again after insert
            $userProgressState = $userProgressStateRepository->getUserProgressStateFromUidAndGameConfigId($uid, $gameConfigId);

            //if still null then something went wrong after insert
            if ($userProgressState === null) {
                throw new RuntimeException('User progress state could not be loaded after creation!!');
            }
        }



        //now we have progress row either from existing state or newly created state
        //now we will get question order and current question index and then we will get current question and answer options and then we will build response data and return it


        $questionIdOrder = $userProgressState->getQuestionIdOrder();

        //question order must always be array
        if (!is_array($questionIdOrder)) {
            throw new RuntimeException('User progress state question order is invalid!!');
        }

        //clean it again for safety because service depends on clean integer ids
        $questionIdOrder = $this->getSanitizedQuestionIdOrder($questionIdOrder);

        //if order becomes empty at runtime that is invalid state
        if (count($questionIdOrder) === 0) {
            throw new RuntimeException('User progress state question order is empty!!');
        }



        $questionIdOrderIndexCurrent = $userProgressState->getQuestionIdOrderIndexCurrent();
        $questionsDone = $questionIdOrderIndexCurrent; //questions done is now derived directly from current index
        $scoreCurrent = $userProgressState->getScoreCurrent();
        $scoreHighest = $userProgressState->getScoreHighest();
        $playCount = $userProgressState->getPlayCount();

        if ($questionIdOrderIndexCurrent < 0) {
            throw new RuntimeException('User progress state current index is invalid!!');
        }

        if ($questionsDone < 0 || $scoreCurrent < 0 || $scoreHighest < 0 || $playCount < 0) {
            throw new RuntimeException('User progress state progress state is invalid!!');
        }

        //derive completion state instead of reading old is_complete column
        if ($this->getIsQuizComplete($questionIdOrder, $questionIdOrderIndexCurrent) === true) {
            return [
                'uid' => $uid,
                'gameConfigId' => $gameConfigId,
                'gameConfigName' => $gameConfigCurrent->getGameConfigName(),
                'score' => $scoreCurrent,
                'scoreHighest' => $scoreHighest,
                'playCount' => $playCount,
                'questionsDone' => $questionsDone,
                'questionCountTotal' => count($questionIdOrder),
                'isQuizDone' => true,
                //return full link because result api now needs both uid and gameConfigId
                'resultLink' => 'quizResultShow.php?uid=' . $uid . '&gameConfigId=' . $gameConfigId
            ];
        }

        //if not correct index for the ques id order throw error
        if (!isset($questionIdOrder[$questionIdOrderIndexCurrent])) {
            throw new RuntimeException('Current question index is out of bounds!!');
        }

        //if ques id we get not numeric then also throw error
        if (!is_numeric($questionIdOrder[$questionIdOrderIndexCurrent])) {
            throw new RuntimeException('Current question id is invalid!!');
        }

        //initialize quiz with teh ques id order that we have got
        $quiz = new Quiz($questionIdOrder);

        //derive current question id directly from order array + current index
        $questionIdCurrent = (int)$questionIdOrder[$questionIdOrderIndexCurrent];
        $questionCurrent = $quiz->getQuestionFromId($questionIdCurrent);

        //if we have no question for that ques id
        if ($questionCurrent->getId() <= 0) {
            throw new RuntimeException('Question with current id was not found!!');
        }

        //now bring ans op for the ques current that is being shown to user
        $answerOptionsForQuestionCurrent = $quiz->getAnswerOptionsForQuestion($questionCurrent);

        //if no ans options found wow
        if (count($answerOptionsForQuestionCurrent) === 0) {
            throw new RuntimeException('No answer options found for current question!!');
        }

        //we need detail of all ans options now
        $answerOptionsForQuestionCurrentDetail = [];

        //loop through all options to get their details and put in array to send to frontend
        foreach ($answerOptionsForQuestionCurrent as $answerOptionForQuestionCurrentTemp) {
            $answerOptionsForQuestionCurrentDetail[] = [
                'id' => $answerOptionForQuestionCurrentTemp->getId(),
                'type' => $answerOptionForQuestionCurrentTemp->getType(),
                'text' => $answerOptionForQuestionCurrentTemp->getText()
            ];
        }


        //now return al data to frontend whatever we have
        return [
            'uid' => $uid,
            'gameConfigId' => $gameConfigId,
            'gameConfigName' => $gameConfigCurrent->getGameConfigName(),
            'score' => $scoreCurrent,
            'scoreHighest' => $scoreHighest,
            'playCount' => $playCount,
            'questionsDone' => $questionsDone,
            'questionIdCurrent' => $questionCurrent->getId(),
            'questionTextCurrent' => $questionCurrent->getText(),
            'questionTypeCurrent' => $questionCurrent->getType(),
            'answerOptionsCurrent' => $answerOptionsForQuestionCurrentDetail,
            'questionCountTotal' => count($questionIdOrder),
            'isQuizDone' => false
        ];
    }

    //a new function to get ques id order from selected game config and also apply feature flag for randomization if enabled and also apply question count target to limit the number of questions based on config
    private function getQuestionIdOrderFromConfig(GameConfig $gameConfigCurrent): array
    {
        //default take from config constants first in case config row has some bad values
        $questionCountTarget = GAME_CONFIG_QUESTION_COUNT_TARGET_DEFAULT;
        $questionIdListAllowed = GAME_CONFIG_QUESTION_ID_LIST_ALLOWED_DEFAULT;

        //read target from selected config if valid
        if ($gameConfigCurrent->getQuestionCountTarget() > 0) {
            $questionCountTarget = $gameConfigCurrent->getQuestionCountTarget();
        }

        //read allowed question ids from selected config if valid
        if (count($gameConfigCurrent->getQuestionIdListAllowed()) > 0) {
            $questionIdListAllowed = $gameConfigCurrent->getQuestionIdListAllowed();
        }

        //sanitize ids before using them
        $questionIdListAllowed = $this->getSanitizedQuestionIdOrder($questionIdListAllowed);

        //if after sanitization there is nothing, just return empty array and caller will handle failure
        if (count($questionIdListAllowed) === 0) {
            return [];
        }

        //also see random feature flag is enables or not
        if (getFeatureFlag() === true) {
            shuffle($questionIdListAllowed); //if enabled shuffle the questions
        }

        //limit the number of questions based on config target and map final ids to int
        return array_map('intval', array_slice($questionIdListAllowed, 0, $questionCountTarget)); //0 is for starting idx
    }
}
