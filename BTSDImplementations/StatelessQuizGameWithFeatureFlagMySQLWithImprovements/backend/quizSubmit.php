<?php
declare(strict_types=1);

require_once 'config.php';
require_once 'question.php';
require_once 'answerOption.php';
require_once 'quiz.php';



require_once __DIR__ . '/../database/repository/quizAttemptRepository.php';


header('Content-Type: application/json');

//check post methods ofc like before all
if (
    !isset($_POST['quizAttemptId']) ||
    !isset($_POST['answerOptionId']) ||
    !is_numeric($_POST['quizAttemptId']) ||
    !is_numeric($_POST['answerOptionId'])
)
    {
        http_response_code(400);
        echo json_encode([
            'error'=>'quizAttemptId and answerOptionId must be numeric!!'
        ]);
        exit;
    }

//get the http method variables
$quizAttemptId=(int)$_POST['quizAttemptId'];
$answerOptionIdByUser=(int)$_POST['answerOptionId'];

//check if valid or not
if ($quizAttemptId<=0 || $answerOptionIdByUser<=0)
    {
        http_response_code(400);
        echo json_encode([
            'error'=>'quizAttemptId and answerOptionId must be positive integers!!'
        ]);
        exit;
    }

//initialize our varibales

$quiz=new Quiz();
$quizAttemptRepository=new QuizAttemptRepository();

$quizAttempt=$quizAttemptRepository->getQuizAttemptFromId($quizAttemptId);

//same check each time 
if ($quizAttempt===null)
    {
        http_response_code(404);
        echo json_encode([
            'error'=>'Quiz attempt not found!!'
        ]);
        exit;
    }

if (!isset($quizAttempt['questionIdOrder']) || !is_array($quizAttempt['questionIdOrder']))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Quiz attempt question order is invalid!!'
        ]);
        exit;
    }

if (!isset($quizAttempt['questionIdOrderIndexCurrent']))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Quiz attempt current index is missing!!'
        ]);
        exit;
    }

if (!isset($quizAttempt['questionIdCurrent']))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Quiz attempt current question id is missing!!'
        ]);
        exit;
    }

if (!isset($quizAttempt['scoreCurrent']) || !isset($quizAttempt['questionsDone']))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Quiz attempt progress state is invalid!!'
        ]);
        exit;
    }

if ($quizAttempt['isComplete']===true)
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Quiz attempt is already complete!!'
        ]);
        exit;
    }

$questionIdOrder=$quizAttempt['questionIdOrder'];
$questionIdOrderIndexCurrent=(int)$quizAttempt['questionIdOrderIndexCurrent'];
$questionIdCurrent=(int)$quizAttempt['questionIdCurrent'];

if (!isset($questionIdOrder[$questionIdOrderIndexCurrent]))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Current question index is out of bounds!!'
        ]);
        exit;
    }

if (!is_numeric($questionIdOrder[$questionIdOrderIndexCurrent]))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Current question id in order is invalid!!'
        ]);
        exit;
    }

if ((int)$questionIdOrder[$questionIdOrderIndexCurrent] !== $questionIdCurrent)
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Quiz attempt current question state is inconsistent!!'
        ]);
        exit;
    }

$questionCurrent=$quiz->getQuestionFromId($questionIdCurrent);
$answerOptionCurrent=$quiz->getAnswerOptionFromId($answerOptionIdByUser);

if ($questionCurrent->getId()<=0 || $answerOptionCurrent->getId()<=0)
    {
        http_response_code(404);
        echo json_encode([
            'error'=>'Question or answer option not found!!'
        ]);
        exit;
    }

if ($answerOptionCurrent->getQuestionId() !== $questionIdCurrent)
    {
        http_response_code(400);
        echo json_encode([
            'error'=>'Answer option does not belong to current question!!'
        ]);
        exit;
    }

$isAnswerOptionCorrectForQuestion=false;

$scoreCurrent=(int)$quizAttempt['scoreCurrent'];
$questionsDone=(int)$quizAttempt['questionsDone'];

if ($quiz->isAnswerOptionCorrect($answerOptionCurrent, $questionCurrent)===true)
    {
        $isAnswerOptionCorrectForQuestion=true;
        $scoreCurrent++;
    }

$questionsDone++;
$questionIdOrderIndexNext=$questionIdOrderIndexCurrent + 1;

if (!isset($questionIdOrder[$questionIdOrderIndexNext]))
    {
        $quizAttemptRepository->markQuizAttemptComplete(
            $quizAttemptId,
            $scoreCurrent,
            $questionsDone,
            $questionIdOrderIndexCurrent,
            $questionIdCurrent
        );

        echo json_encode([
            'quizAttemptId'=>$quizAttemptId,
            'score'=>$scoreCurrent,
            'questionsDone'=>$questionsDone,
            'questionCountTotal'=>count($quiz->getQuestions()),
            'isAnswerOptionCorrectForQuestion'=>$isAnswerOptionCorrectForQuestion,
            'isQuizDone'=>true,
            'resultLink'=>'quizResultShow.php'
        ]);
        exit;
    }

if (!is_numeric($questionIdOrder[$questionIdOrderIndexNext]))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Next question id is invalid!!'
        ]);
        exit;
    }

$questionIdNext=(int)$questionIdOrder[$questionIdOrderIndexNext];

//update the progress of quiz in the db
$quizAttemptRepository->updateQuizAttemptProgress(
    $quizAttemptId,
    $scoreCurrent,
    $questionsDone,
    $questionIdOrderIndexNext,
    $questionIdNext
);

echo json_encode([
    'quizAttemptId'=>$quizAttemptId,
    'score'=>$scoreCurrent,
    'questionsDone'=>$questionsDone,
    'questionCountTotal'=>count($quiz->getQuestions()),
    'isAnswerOptionCorrectForQuestion'=>$isAnswerOptionCorrectForQuestion,
    'isQuizDone'=>false,
    'questionIdNext'=>$questionIdNext
]);
