<?php
declare(strict_types=1);

require_once 'config.php';
require_once 'question.php';
require_once 'answerOption.php';
require_once 'quiz.php';
require_once 'featureFlag.php';

require_once __DIR__ . '/../database/repository/quizAttemptRepository.php';

//since we r gonna try in terminal mode testing first 
// - so for that mentioning this line is imp
header('Content-Type: application/json');

//so now finally initialize quiz
$quiz=new Quiz();

$quizAttemptRepository=new QuizAttemptRepository();


//initialize attempt id with null so we know that it wasn't set up earlier so we have to start a new session for this attempt coz otherwise it will get updated with get request
$quizAttemptId=null;


//now lets say the quizAttemptId already exists
if (isset($_GET['quizAttemptId']))
    {
        //so it should be first of all valid like numeric 
        //ofc can be alphanumeric in production scenarios but for now its numeric
        if (!is_numeric($_GET['quizAttemptId']))
            {
                //ERROR ERROR
                http_response_code(400);
                echo json_encode([
                    'error'=>'Invalid attempt id, it should be numeric!!'
                ]);
                exit; //DO NOT FORGET THIS
            }

        //now even if numerical it is, still it should be valid right
        $quizAttemptId=(int)$_GET['quizAttemptId'];
        if ($quizAttemptId<=0)
            {
                //ERROR ERROR
                http_response_code(400);
                echo json_encode([
                    'error'=>'Invalid attempt id, it should be positive number!!'
                ]);
                exit; //DO NOT FORGET THIS
            }
    }



//now still if quizAttemptId is null then we need a new session saved in db
if ($quizAttemptId===null)
    {
        $questionIdOrder=$quiz->getQuestionIdOrdering();
        
        //now if no order we got
        if (count($questionIdOrder)===0)
            {
                //ERROR ERROR
                http_response_code(404);
                echo json_encode([
                    'error'=>'No questions were found in the array!!'
                ]);
                exit; //DO NOT FORGET THIS
            }


        //but if there are question ids in the array
        //then is feature flag true, if yes shuffle otherwise leave


        if (getFeatureFlag()===true)
            {
                shuffle($questionIdOrder);
            }


        $quizAttemptId=$quizAttemptRepository->createQuizAttempt($questionIdOrder);

        if ($quizAttemptId<=0)
            {
                //ERROR ERROR
                http_response_code(500);
                echo json_encode([
                    'error'=>'Failed to create quizAttemptId!!'
                ]);
                exit; //DO NOT FORGET THIS
            }

    }

//now call command to insert a row in the quizAttempts table
$quizAttempt=$quizAttemptRepository->getQuizAttemptFromId($quizAttemptId);

//what if we got nothing from this, raise error
if ($quizAttempt===null)
    {
        http_response_code(404);
        echo json_encode([
            'error'=>'Quiz attempt not found!!'
        ]);
        exit;
    }

//what if we do not get the question order correctly , it should be array also
if (!isset($quizAttempt['questionIdOrder']) || !is_array($quizAttempt['questionIdOrder']))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Quiz attempt question order is invalid!!'
        ]);
        exit;
    }

//now what if we do not have the correct index of the ques id order array
if (!isset($quizAttempt['questionIdOrderIndexCurrent']))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Quiz attempt current index is missing!!'
        ]);
        exit;
    }

//now what if we dont have any progress state already so it should be like quesdone should be there as well as some current score ofc
if (!isset($quizAttempt['questionsDone']) || !isset($quizAttempt['scoreCurrent']))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Quiz attempt progress state is invalid!!'
        ]);
        exit;
    }

//what if quizattempt already done, return good nice http response and just go to result show
if ($quizAttempt['isComplete']===true)
    {
        http_response_code(200);
        echo json_encode([
            'quizAttemptId'=>$quizAttemptId,
            'isQuizDone'=>true,
            'resultLink'=>'quizResultShow.php'
        ]);
        exit;
    }

//if not all this then letss go further,, take ques id order and show in that order
$questionIdOrder=$quizAttempt['questionIdOrder'];
$questionIdOrderIndexCurrent=(int)$quizAttempt['questionIdOrderIndexCurrent'];

//now took index but it hsould be in bounds ofc
if (!isset($questionIdOrder[$questionIdOrderIndexCurrent]))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Current question index is out of bounds!!'
        ]);
        exit;
    }

//ques id itself is invalid no wayyyyyy
if (!is_numeric($questionIdOrder[$questionIdOrderIndexCurrent]))
    {
        http_response_code(409);
        echo json_encode([
            'error'=>'Current question id is invalid!!'
        ]);
        exit;
    }

//now if not all this and everything still fine then take current id of ques 
$questionIdCurrent=(int)$questionIdOrder[$questionIdOrderIndexCurrent];
$questionCurrent=$quiz->getQuestionFromId($questionIdCurrent);

//now if no ques with that id no wayyyyyy
if ($questionCurrent->getId()<=0)
    {
        http_response_code(404);
        echo json_encode([
            'error'=>'Question with the given id is not found!!'
        ]);
        exit;
    }

//if ques id correct, fetch ans options now for that id
$answerOptionsForQuestionCurrent=$quiz->getAnswerOptionsForQuestion($questionCurrent);

//we got no ans op now great
if (count($answerOptionsForQuestionCurrent)===0)
    {
        http_response_code(404);
        echo json_encode([
            'error'=>'No answer options found for current question!!'
        ]);
        exit;
    }

//now we need detail of each ans op if it was not empty which it should be not ofc
$answerOptionsForQuestionCurrentDetail=[];

foreach ($answerOptionsForQuestionCurrent as $answerOptionForQuestionCurrentTemp)
    {
        $answerOptionsForQuestionCurrentDetail[]=[
            'id'=>$answerOptionForQuestionCurrentTemp->getId(),
            'type'=>$answerOptionForQuestionCurrentTemp->getType(),
            'text'=>$answerOptionForQuestionCurrentTemp->getText()
        ];
    }


//now just return ig hope so no check left hehe
echo json_encode([
    'quizAttemptId'=>$quizAttemptId,
    'score'=>$quizAttempt['scoreCurrent'],
    'questionsDone'=>$quizAttempt['questionsDone'],
    'questionIdCurrent'=>$questionCurrent->getId(),
    'questionTextCurrent'=>$questionCurrent->getText(),
    'questionTypeCurrent'=>$questionCurrent->getType(),
    'answerOptionsCurrent'=>$answerOptionsForQuestionCurrentDetail,
    'questionCountTotal'=>count($quiz->getQuestions()),
    'isQuizDone'=>false
]);

?>










