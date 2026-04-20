<?php
declare(strict_types=1);


session_start();


require_once 'config.php';
require_once 'question.php';
require_once 'answerOption.php';
// require_once 'quizData.php';
require_once 'quiz.php';


header ('Content-Type: application/json');  //no extra space before this colon girl, be careful

if (!isset($_SESSION['score']) || !isset($_SESSION['questionsDone']))
    {
        http_response_code(409);
        echo json_encode([
                'error'=>"Failed to load results coz unavailable!!"
        ]);
        exit;
    }

//initialize quiz to get variables
$quiz=new Quiz();


//variables value retrieval
$score=$_SESSION['score']; //will be int
$questionsDone=$_SESSION['questionsDone'];
$questionCountTotal=count($quiz->getQuestions());
$answerCountWrong=$questionsDone-$score;

//NEW INCOMING CHECK
if ($questionsDone<$questionCountTotal)
    {
        http_response_code(409);
        echo json_encode([
            'error' => 'Quiz is not complete yet!!!'
        ]);
        exit;
    }

//calculation of the score
if ($questionsDone>0)
    {
        $scorePercentage=(int)round(($score/$questionCountTotal)*100);
    }
else $scorePercentage=0;


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
else{
    $resultText='TRY AGAIN FOR A BETTER SCORE!!';
}


echo json_encode([
    'score'=> $_SESSION['score'],
    'questionsDone'=> $_SESSION['questionsDone'],
    'questionCountTotal'=> count($quiz->getQuestions()),
    'resultText'=>$resultText,
    'scorePercentage'=>$scorePercentage,
    'answerCountWrong'=>$answerCountWrong
]);



session_unset();
session_destroy();

?>