<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../entity/question.php';
require_once __DIR__ . '/../entity/answerOption.php';
require_once __DIR__ . '/../entity/quiz.php';

require_once __DIR__ . '/../../database/repository/userProgressStateRepository.php';

//so now this should do following::

// get quiz progress state
// validate quiz state is consistent or not
// get current question
// get answer option
// check correctness
// update score
// move to next question
// mark complete if yes
// return submit response data


class QuizSubmitService
{
    //this helper will decide completion using derived logic only
    private function getIsQuizComplete(array $questionIdOrder, int $questionIdOrderIndexCurrent): bool
    {
        $questionCountTotal = count($questionIdOrder);

        if ($questionIdOrderIndexCurrent >= $questionCountTotal) {
            return true;
        }

        return false;
    }

    //this will take uid and ans op id too from user as input and then it will return data that will help in showing next question or result on frontend
    public function quizSubmitService(int $uid, int $gameConfigId, int $answerOptionIdByUser): array
    {
        //uid must be valid
        if ($uid <= 0) {
            throw new InvalidArgumentException('Invalid uid for quiz submit!!');
        }

        //config id must be valid because submit is now per config
        if ($gameConfigId <= 0) {
            throw new InvalidArgumentException('Invalid gameConfigId for quiz submit!!');
        }

        //answer option id must be valid too
        if ($answerOptionIdByUser <= 0) {
            throw new InvalidArgumentException('Invalid answerOptionId for quiz submit!!');
        }

        $userProgressStateRepository = new UserProgressStateRepository();

        //load exact progress row for this user+config pair
        $userProgressState = $userProgressStateRepository->getUserProgressStateFromUidAndGameConfigId($uid, $gameConfigId);

        if ($userProgressState === null) {
            throw new RuntimeException('User progress state not found!!');
        }

        $questionIdOrder = $userProgressState->getQuestionIdOrder();
        $questionIdOrderIndexCurrent = $userProgressState->getQuestionIdOrderIndexCurrent();

        if (!is_array($questionIdOrder)) {
            throw new RuntimeException('User progress state question order is invalid!!');
        }

        if ($questionIdOrderIndexCurrent < 0) {
            throw new RuntimeException('User progress state current index is invalid!!');
        }

        //if already complete, submit should not proceed
        if ($this->getIsQuizComplete($questionIdOrder, $questionIdOrderIndexCurrent) === true) {
            throw new RuntimeException('User progress state is already complete!!');
        }

        if (!isset($questionIdOrder[$questionIdOrderIndexCurrent])) {
            throw new RuntimeException('Current question index is out of bounds!!');
        }

        if (!is_numeric($questionIdOrder[$questionIdOrderIndexCurrent])) {
            throw new RuntimeException('Current question id in order is invalid!!');
        }

        //derive current question id from order array instead of old question_id_current column
        $questionIdCurrent = (int)$questionIdOrder[$questionIdOrderIndexCurrent];

        $quiz = new Quiz($questionIdOrder);

        $questionCurrent = $quiz->getQuestionFromId($questionIdCurrent);
        $answerOptionCurrent = $quiz->getAnswerOptionFromId($answerOptionIdByUser);

        if ($questionCurrent->getId() <= 0 || $answerOptionCurrent->getId() <= 0) {
            throw new RuntimeException('Question or answer option not found!!');
        }

        if ($answerOptionCurrent->getQuestionId() !== $questionIdCurrent) {
            throw new InvalidArgumentException('Answer option does not belong to current question!!');
        }

        $isAnswerOptionCorrectForQuestion = false;
        $scoreCurrent = $userProgressState->getScoreCurrent();
        $scoreHighest = $userProgressState->getScoreHighest();
        $playCount = $userProgressState->getPlayCount();
        $questionsDone = $questionIdOrderIndexCurrent; //derived questions done before submit

        //so if ans op correct, just increment score
        if ($quiz->isAnswerOptionCorrect($answerOptionCurrent, $questionCurrent) === true) {
            $isAnswerOptionCorrectForQuestion = true;
            $scoreCurrent++;
        }

        //increment no of ques done as well by moving current index to next slot
        $questionIdOrderIndexNext = $questionIdOrderIndexCurrent + 1;
        $questionsDone = $questionIdOrderIndexNext;

        //highest score should be preserved as best ever for same config
        if ($scoreCurrent > $scoreHighest) {
            $scoreHighest = $scoreCurrent;
        }

        //if next ques idx is out of bounds then quiz becomes complete now
        if (!isset($questionIdOrder[$questionIdOrderIndexNext])) {
            //update same progress row to final completed state
            //completion is now derived because next index reaches total question count
            $userProgressStateRepository->updateUserProgressState(
                $uid,
                $gameConfigId,
                $scoreCurrent,
                $scoreHighest,
                $playCount,
                $questionIdOrder,
                $questionIdOrderIndexNext
            );

            return [
                'uid' => $uid,
                'gameConfigId' => $gameConfigId,
                'score' => $scoreCurrent,
                'scoreHighest' => $scoreHighest,
                'playCount' => $playCount,
                'questionsDone' => $questionsDone,
                'questionCountTotal' => count($questionIdOrder),
                'isAnswerOptionCorrectForQuestion' => $isAnswerOptionCorrectForQuestion,
                'isQuizDone' => true,
                //return full link because result api now needs both uid and gameConfigId
                'resultLink' => 'quizResultShow.php?uid=' . $uid . '&gameConfigId=' . $gameConfigId
            ];
        }

        //if next ques idx is not out of bounds then also check if the ques id at that idx is valid or not
        if (!is_numeric($questionIdOrder[$questionIdOrderIndexNext])) {
            throw new RuntimeException('Next question id is invalid!!');
        }

        //if everything is fine then move to next ques by updating user progress state in db
        $questionIdNext = (int)$questionIdOrder[$questionIdOrderIndexNext];

        //update user progress state with new progress
        $userProgressStateRepository->updateUserProgressState(
            $uid,
            $gameConfigId,
            $scoreCurrent,
            $scoreHighest,
            $playCount,
            $questionIdOrder,
            $questionIdOrderIndexNext
        );

        return [
            'uid' => $uid,
            'gameConfigId' => $gameConfigId,
            'score' => $scoreCurrent,
            'scoreHighest' => $scoreHighest,
            'playCount' => $playCount,
            'questionsDone' => $questionsDone,
            'questionCountTotal' => count($questionIdOrder),
            'isAnswerOptionCorrectForQuestion' => $isAnswerOptionCorrectForQuestion,
            'isQuizDone' => false,
            'questionIdNext' => $questionIdNext
        ];
    }
}
