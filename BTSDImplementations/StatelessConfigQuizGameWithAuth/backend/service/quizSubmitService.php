<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../entity/question.php';
require_once __DIR__ . '/../entity/answerOption.php';
require_once __DIR__ . '/../entity/quiz.php';

require_once __DIR__ . '/../../database/repository/quizAttemptRepository.php';


//so now this should do follwokgn::

// get quiz attempt
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
    //this will take quiz attempt id and ans op id too from user as input and then it will return data that will help in showing next question or result on frontend
    public function quizSubmitService(int $quizAttemptId,int $answerOptionIdByUser):array
    {
        $quizAttemptRepository=new QuizAttemptRepository();
        $quizAttempt=$quizAttemptRepository->getQuizAttemptFromId($quizAttemptId);

        //same checks as all the files
        if ($quizAttempt===null)
            {
                throw new RuntimeException('Quiz attempt not found!!');
            }

        if (!isset($quizAttempt['questionIdOrder']) || !is_array($quizAttempt['questionIdOrder']))
            {
                throw new RuntimeException('Quiz attempt question order is invalid!!');
            }

        if (!isset($quizAttempt['questionIdOrderIndexCurrent']))
            {
                throw new RuntimeException('Quiz attempt current index is missing!!');
            }

        if (!isset($quizAttempt['questionIdCurrent']))
            {
                throw new RuntimeException('Quiz attempt current question id is missing!!');
            }

        if (!isset($quizAttempt['scoreCurrent']) || !isset($quizAttempt['questionsDone']))
            {
                throw new RuntimeException('Quiz attempt progress state is invalid!!');
            }

        if ($quizAttempt['isComplete']===true)
            {
                throw new RuntimeException('Quiz attempt is already complete!!');
            }

        //take ques id order and current idx and current ques id from quiz attempt data for futher processing
        $questionIdOrder=$quizAttempt['questionIdOrder'];
        $questionIdOrderIndexCurrent=$quizAttempt['questionIdOrderIndexCurrent'];
        $questionIdCurrent=$quizAttempt['questionIdCurrent'];

        //same checks again
        if (!isset($questionIdOrder[$questionIdOrderIndexCurrent]))
            {
                throw new RuntimeException('Current question index is out of bounds!!');
            }

        if (!is_numeric($questionIdOrder[$questionIdOrderIndexCurrent]))
            {
                throw new RuntimeException('Current question id in order is invalid!!');
            }

        if ((int)$questionIdOrder[$questionIdOrderIndexCurrent] !== $questionIdCurrent)
            {
                throw new RuntimeException('Quiz attempt current question state is inconsistent!!');
            }

        $quiz=new Quiz($questionIdOrder);

        $questionCurrent=$quiz->getQuestionFromId($questionIdCurrent);
        $answerOptionCurrent=$quiz->getAnswerOptionFromId($answerOptionIdByUser);

        if ($questionCurrent->getId()<=0 || $answerOptionCurrent->getId()<=0)
            {
                throw new RuntimeException('Question or answer option not found!!');
            }

        if ($answerOptionCurrent->getQuestionId() !== $questionIdCurrent)
            {
                throw new InvalidArgumentException('Answer option does not belong to current question!!');
            }

        $isAnswerOptionCorrectForQuestion=false;
        $scoreCurrent=$quizAttempt['scoreCurrent'];
        $questionsDone=$quizAttempt['questionsDone'];

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
                $quizAttemptRepository->markQuizAttemptComplete(
                    $quizAttemptId,
                    $scoreCurrent,
                    $questionsDone,
                    $questionIdOrderIndexCurrent,
                    $questionIdCurrent
                );

                return [
                    'quizAttemptId'=>$quizAttemptId,
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

        //if everything is fine then move to next ques by updating quiz attempt in db with next ques id and next ques idx and also update score and ques done count
        $questionIdNext=(int)$questionIdOrder[$questionIdOrderIndexNext];

        //udpate quiz attempt with new progress
        $quizAttemptRepository->updateQuizAttemptProgress(
            $quizAttemptId,
            $scoreCurrent,
            $questionsDone,
            $questionIdOrderIndexNext,
            $questionIdNext
        );

        return [
            'quizAttemptId'=>$quizAttemptId,
            'score'=>$scoreCurrent,
            'questionsDone'=>$questionsDone,
            'questionCountTotal'=>count($questionIdOrder),
            'isAnswerOptionCorrectForQuestion'=>$isAnswerOptionCorrectForQuestion,
            'isQuizDone'=>false,
            'questionIdNext'=>$questionIdNext
        ];
    }
}
