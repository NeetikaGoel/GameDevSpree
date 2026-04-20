<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

require_once __DIR__ . '/../../database/repository/userProgressStateRepository.php';


//now what this service has to do

// get attempt by ID
// verify attempt complete
// calculate wrong answers
// calculate score percentage
// decide result text
// return result data



class QuizResultShowService
{
    //it will take uid now as parameter and return data that will help in showing result on frontend
    public function quizResultShowService(int $uid):array
    {
        $userProgressStateRepository=new UserProgressStateRepository();
        $userProgressState=$userProgressStateRepository->getUserProgressStateFromUid($uid);

        if ($userProgressState===null)
            {
                throw new RuntimeException('User progress state not found!');
            }

        $score=$userProgressState->getScoreCurrent();
        $questionsDone=$userProgressState->getQuestionsDone();
        $questionIdOrder=$userProgressState->getQuestionIdOrder();

        if ($score<0 || $questionsDone<0)
            {
                throw new RuntimeException('User progress state is invalid.');
            }

        if (!is_array($questionIdOrder))
            {
                throw new RuntimeException('User progress state question order is invalid.');
            }

        if ($userProgressState->getIsComplete()!==true)
            {
                throw new RuntimeException('Quiz is not complete yet.');
            }

        //now we will calculate the score percentage and decide the result text based on that and also calculate wrong answer count and return all that data in an array
        $questionCountTotal=count($questionIdOrder);
        $answerCountWrong=$questionsDone-$score;

        if ($questionCountTotal<=0)
            {
                throw new RuntimeException('Question count total is invalid.');
            }

        if ($answerCountWrong<0)
            {
                throw new RuntimeException('Answer count wrong is invalid.');
            }

        if ($questionsDone>0)
            {
                $scorePercentage=(int)round(($score/$questionCountTotal)*100);
            }
        else
            {
                $scorePercentage=0;
            }

        $resultText='';

        if ($scorePercentage===100)
            {
                $resultText='CONGRATULATIONS!! PERFECT SCORE!!';
            }
        else if ($scorePercentage>=80)
            {
                $resultText='GREAT EFFORTS!! CAN DO BETTER!!';
            }
        else if ($scorePercentage>=50)
            {
                $resultText='WELL DONE!! WANNA TRY AGAIN?';
            }
        else
            {
                $resultText='TRY AGAIN FOR A BETTER SCORE!!';
            }

        return [
            'uid'=>$uid,
            'score'=>$score,
            'questionsDone'=>$questionsDone,
            'questionCountTotal'=>$questionCountTotal,
            'resultText'=>$resultText,
            'scorePercentage'=>$scorePercentage,
            'answerCountWrong'=>$answerCountWrong
        ];
    }
}