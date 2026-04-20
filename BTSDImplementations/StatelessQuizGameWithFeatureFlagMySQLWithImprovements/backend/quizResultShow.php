<?php
declare(strict_types=1);

session_start();

require_once 'config.php';
require_once 'question.php';
require_once 'answerOption.php';
require_once 'quiz.php';


header('Content-Type: application/json');

require_once __DIR__ . '/../database/repository/quizAttemptRepository.php';

//invalid id check hehe
if (!isset($_GET['quizAttemptId']) || !is_numeric($_GET['quizAttemptId']))
    {
        http_response_code(400);
        echo json_encode([
            'error'=>'quizAttemptId must be numeric!!'
        ]);
        exit;
    }

//now take quiz attempt id if correct
$quizAttemptId=(int)$_GET['quizAttemptId'];


//now check if its positive or not
if ($quizAttemptId<=0)
    {
        http_response_code(400);
        echo json_encode([
            'error'=>'quizAttemptId must be a positive integer!!'
        ]);
        exit;
    }


//initialize new variables for both

$quiz=new Quiz();
$quizAttemptRepository=new QuizAttemptRepository();

$quizAttempt=$quizAttemptRepository->getQuizAttemptFromId($quizAttemptId);

//no quiz attempt error error now
if ($quizAttempt===null)
    {
        http_response_code(404);
        echo json_encode([
            'error'=>'Quiz attempt not found!!'
        ]);
        exit;
    }

//check qiuz attempt parameters toooo
if (!isset($quizAttempt['scoreCurrent']) || !isset($quizAttempt['questionsDone']))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Quiz attempt progress state is invalid!!'
        ]);
        exit;
    }

//it should not be already done ofc
if ($quizAttempt['isComplete']!==true)
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Quiz is not complete yet!!'
        ]);
        exit;
    }

//take all variables to show the result later and save it too
$score=(int)$quizAttempt['scoreCurrent'];
$questionsDone=(int)$quizAttempt['questionsDone'];
$questionCountTotal=count($quiz->getQuestions());
$answerCountWrong=$questionsDone - $score;

//some ques count shoudl be there check it
if ($questionCountTotal<=0)
    {
        http_response_code(500);
        echo json_encode([
            'error'=>'Question count total is invalid!!'
        ]);
        exit;
    }

//atleast some ques must be done then here otherwise how on thsi page why whyyyy
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


//now final result show hehe
echo json_encode([
    'quizAttemptId'=>$quizAttemptId,
    'score'=>$score,
    'questionsDone'=>$questionsDone,
    'questionCountTotal'=>$questionCountTotal,
    'resultText'=>$resultText,
    'scorePercentage'=>$scorePercentage,
    'answerCountWrong'=>$answerCountWrong
]);



?>