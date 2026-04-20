<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

require_once __DIR__ . '/../../database/repository/quizAttemptRepository.php';


//now what this service has to do

// get attempt by ID
// verify attempt complete
// calculate wrong answers
// calculate score percentage
// decide result text
// return result data



class QuizResultShowService
{
    //it will take quizattempt id as parameter and return data that will help in showing result on frontend
    public function quizResultShowService(int $quizAttemptId):array
    {
        $quizAttemptRepository=new QuizAttemptRepository();
        $quizAttempt=$quizAttemptRepository->getQuizAttemptFromId($quizAttemptId);

        if ($quizAttempt===null)
            {
                throw new RuntimeException('Quiz attempt not found.');
            }

        if (!isset($quizAttempt['scoreCurrent']) || !isset($quizAttempt['questionsDone']))
            {
                throw new RuntimeException('Quiz attempt progress state is invalid.');
            }

        if (!isset($quizAttempt['questionIdOrder']) || !is_array($quizAttempt['questionIdOrder']))
            {
                throw new RuntimeException('Quiz attempt question order is invalid.');
            }

        if ($quizAttempt['isComplete']!==true)
            {
                throw new RuntimeException('Quiz is not complete yet.');
            }

        //now we will calculate the score percentage and decide the result text based on that and also calculate wrong answer count and return all that data in an array
        $score=(int)$quizAttempt['scoreCurrent'];
        $questionsDone=(int)$quizAttempt['questionsDone'];
        $questionCountTotal=count($quizAttempt['questionIdOrder']);
        $answerCountWrong=$questionsDone-$score;

        if ($questionCountTotal<=0)
            {
                throw new RuntimeException('Question count total is invalid.');
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
            'quizAttemptId'=>$quizAttemptId,
            'score'=>$score,
            'questionsDone'=>$questionsDone,
            'questionCountTotal'=>$questionCountTotal,
            'resultText'=>$resultText,
            'scorePercentage'=>$scorePercentage,
            'answerCountWrong'=>$answerCountWrong
        ];
    }
}
