<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../entity/question.php';
require_once __DIR__ . '/../entity/answerOption.php';
require_once __DIR__ . '/../entity/quiz.php';

require_once __DIR__ . '/../../database/repository/userProgressStateRepository.php';

//so now this should do follwoign::

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
    //this will take uid and ans op id too from user as input and then it will return data that will help in showing next question or result on frontend
    public function quizSubmitService(int $uid, int $answerOptionIdByUser):array
    {
        $userProgressStateRepository=new UserProgressStateRepository();
        $userProgressState=$userProgressStateRepository->getUserProgressStateFromUid($uid);

        if ($userProgressState===null)
            {
                throw new RuntimeException('User progress state not found!!');
            }

        $questionIdOrder=$userProgressState->getQuestionIdOrder();
        $questionIdOrderIndexCurrent=$userProgressState->getQuestionIdOrderIndexCurrent();
        $questionIdCurrent=$userProgressState->getQuestionIdCurrent();

        if (!is_array($questionIdOrder))
            {
                throw new RuntimeException('User progress state question order is invalid!!');
            }

        if ($questionIdOrderIndexCurrent<0)
            {
                throw new RuntimeException('User progress state current index is invalid!!');
            }

        if (!isset($questionIdOrder[$questionIdOrderIndexCurrent]))
            {
                throw new RuntimeException('Current question index is out of bounds!!');
            }

        if (!is_numeric($questionIdOrder[$questionIdOrderIndexCurrent]))
            {
                throw new RuntimeException('Current question id in order is invalid!!');
            }

        if ((int)$questionIdOrder[$questionIdOrderIndexCurrent]!==$questionIdCurrent)
            {
                throw new RuntimeException('User progress state current question state is inconsistent!!');
            }

        if ($userProgressState->getIsComplete()===true)
            {
                throw new RuntimeException('User progress state is already complete!!');
            }

        $quiz=new Quiz($questionIdOrder);

        $questionCurrent=$quiz->getQuestionFromId($questionIdCurrent);
        $answerOptionCurrent=$quiz->getAnswerOptionFromId($answerOptionIdByUser);

        if ($questionCurrent->getId()<=0 || $answerOptionCurrent->getId()<=0)
            {
                throw new RuntimeException('Question or answer option not found!!');
            }

        if ($answerOptionCurrent->getQuestionId()!==$questionIdCurrent)
            {
                throw new InvalidArgumentException('Answer option does not belong to current question!!');
            }

        $isAnswerOptionCorrectForQuestion=false;
        $scoreCurrent=$userProgressState->getScoreCurrent();
        $questionsDone=$userProgressState->getQuestionsDone();

        //so if ans op correct, just increment score
        if ($quiz->isAnswerOptionCorrect($answerOptionCurrent,$questionCurrent)===true)
            {
                $isAnswerOptionCorrectForQuestion=true;
                $scoreCurrent++;
            }

        //increment no of ques done as well
        $questionsDone++;
        //take index to next ques
        $questionIdOrderIndexNext=$questionIdOrderIndexCurrent+1;

        //if next ques idx is out of bounds
        if (!isset($questionIdOrder[$questionIdOrderIndexNext]))
            {
                //then it means quiz is complete now so mark complete in db and return response accordingly
                $userProgressStateRepository->markUserProgressStateComplete(
                    $uid,
                    $scoreCurrent,
                    $questionsDone,
                    $questionIdOrder,
                    $questionIdOrderIndexCurrent,
                    $questionIdCurrent
                );

                return [
                    'uid'=>$uid,
                    'score'=>$scoreCurrent,
                    'questionsDone'=>$questionsDone,
                    'questionCountTotal'=>count($questionIdOrder),
                    'isAnswerOptionCorrectForQuestion'=>$isAnswerOptionCorrectForQuestion,
                    'isQuizDone'=>true,
                    'resultLink'=>'quizResultShow.php'
                ];
            }

        //if next ques idx is not out of bounds then also check if the ques id at that idx is valid or not
        if (!is_numeric($questionIdOrder[$questionIdOrderIndexNext]))
            {
                throw new RuntimeException('Next question id is invalid!!');
            }

        //if everything is fine then move to next ques by updating user progress state in db
        $questionIdNext=(int)$questionIdOrder[$questionIdOrderIndexNext];

        //udpate user progress state with new progress
        $userProgressStateRepository->updateUserProgressState(
            $uid,
            $scoreCurrent,
            $questionsDone,
            $questionIdOrder,
            $questionIdOrderIndexNext,
            $questionIdNext,
            false
        );

        return [
            'uid'=>$uid,
            'score'=>$scoreCurrent,
            'questionsDone'=>$questionsDone,
            'questionCountTotal'=>count($questionIdOrder),
            'isAnswerOptionCorrectForQuestion'=>$isAnswerOptionCorrectForQuestion,
            'isQuizDone'=>false,
            'questionIdNext'=>$questionIdNext
        ];
    }
}