<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

require_once __DIR__ . '/../../database/repository/userProgressStateRepository.php';
require_once __DIR__ . '/../../database/repository/gameConfigRepository.php';


//now what this service has to do

// get attempt by ID
// verify attempt complete
// calculate wrong answers
// calculate score percentage
// decide result text
// return result data



class QuizResultShowService
{
    //this helper will decide completion using derived logic only
    //we no longer store is_complete in db
    private function getIsQuizComplete(array $questionIdOrder, int $questionIdOrderIndexCurrent): bool
    {
        $questionCountTotal = count($questionIdOrder);

        if ($questionIdOrderIndexCurrent >= $questionCountTotal) {
            return true;
        }

        return false;
    }

    //it will take uid now as parameter and return data that will help in showing result on frontend
    public function quizResultShowService(int $uid, int $gameConfigId): array
    {
        //uid must be valid because result is user specific
        if ($uid <= 0) {
            throw new InvalidArgumentException('Invalid uid for quiz result show!!');
        }

        //config id must be valid because result is per config now
        if ($gameConfigId <= 0) {
            throw new InvalidArgumentException('Invalid gameConfigId for quiz result show!!');
        }

        $userProgressStateRepository = new UserProgressStateRepository();
        $gameConfigRepository = new GameConfigRepository();

        //load progress row for exact user+config pair
        $userProgressState = $userProgressStateRepository->getUserProgressStateFromUidAndGameConfigId($uid, $gameConfigId);

        if ($userProgressState === null) {
            throw new RuntimeException('User progress state not found!');
        }

        //load config too because frontend result page may want name/info and also to verify config still exists
        $gameConfigCurrent = $gameConfigRepository->getGameConfigFromId($gameConfigId);

        if ($gameConfigCurrent === null) {
            throw new RuntimeException('Game config not found while loading result!');
        }

        $score = $userProgressState->getScoreCurrent();
        $scoreHighest = $userProgressState->getScoreHighest();
        $playCount = $userProgressState->getPlayCount();
        $questionIdOrder = $userProgressState->getQuestionIdOrder();
        $questionIdOrderIndexCurrent = $userProgressState->getQuestionIdOrderIndexCurrent();

        if ($score < 0 || $scoreHighest < 0 || $playCount < 0 || $questionIdOrderIndexCurrent < 0) {
            throw new RuntimeException('User progress state is invalid.');
        }

        if (!is_array($questionIdOrder)) {
            throw new RuntimeException('User progress state question order is invalid.');
        }

        //quiz must be complete before result can be shown
        if ($this->getIsQuizComplete($questionIdOrder, $questionIdOrderIndexCurrent) !== true) {
            throw new RuntimeException('Quiz is not complete yet.');
        }

        //now we will calculate the score percentage and decide the result text based on that and also calculate wrong answer count and return all that data in an array
        $questionCountTotal = count($questionIdOrder);
        $questionsDone = $questionIdOrderIndexCurrent; //derived questions done from current index because complete run reaches total count
        $answerCountWrong = $questionCountTotal - $score;

        if ($questionCountTotal <= 0) {
            throw new RuntimeException('Question count total is invalid.');
        }

        if ($answerCountWrong < 0) {
            throw new RuntimeException('Answer count wrong is invalid.');
        }

        if ($questionCountTotal > 0) {
            $scorePercentage = (int)round(($score / $questionCountTotal) * 100);
        } else {
            $scorePercentage = 0;
        }

        $resultText = '';

        if ($scorePercentage === 100) {
            $resultText = 'CONGRATULATIONS!! PERFECT SCORE!!';
        } else if ($scorePercentage >= 80) {
            $resultText = 'GREAT EFFORTS!! CAN DO BETTER!!';
        } else if ($scorePercentage >= 50) {
            $resultText = 'WELL DONE!! WANNA TRY AGAIN?';
        } else {
            $resultText = 'TRY AGAIN FOR A BETTER SCORE!!';
        }

        return [
            'uid' => $uid,
            'gameConfigId' => $gameConfigId,
            'gameConfigName' => $gameConfigCurrent->getGameConfigName(),
            'score' => $score,
            'scoreHighest' => $scoreHighest,
            'playCount' => $playCount,
            'questionsDone' => $questionsDone,
            'questionCountTotal' => $questionCountTotal,
            'resultText' => $resultText,
            'scorePercentage' => $scorePercentage,
            'answerCountWrong' => $answerCountWrong,
            'isQuizDone' => true
        ];
    }
}
