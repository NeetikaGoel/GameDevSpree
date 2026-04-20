<?php
declare(strict_types=1);

session_start();

require_once 'config.php';
require_once 'question.php';
require_once 'answerOption.php';
require_once 'quizData.php';
require_once 'quiz.php';


header('Content-Type: application/json');


if (!isset($_POST['questionId']) || !isset($_POST['answerOptionId']) || !is_numeric($_POST['questionId']) || !is_numeric($_POST['answerOptionId']))//check int tooo IT IS BOUNDARY VALIDATIONNNNNNNNN
    {
        http_response_code(500);
        echo json_encode([
            'error'=>"Invalid question id/answer id!!"
        ]);
        exit;
    }


if (!isset($_SESSION['score']) || !isset($_SESSION['questionsDone']) || !isset($_SESSION['questionIdCurrent']))
    {
        http_response_code(500);
        echo json_encode([
            'error'=>"Session not initialized!!"
        ]);
        exit;
    }

//post values give to variables but since they r strings by default change them to int before any further processing!!!!!!
$answerOptionIdByUser=(int)$_POST['answerOptionId'];
$questionIdByUser=(int)$_POST['questionId'];
//explicit is BETTER THAN IMPLICIT SO MENTION ITTTT

$quiz=new Quiz(); //can be without these parenthesis as welllll

$answerOptionCurrent=$quiz->getAnswerOptionFromId($answerOptionIdByUser);
$questionCurrent=$quiz->getQuestionFromId($questionIdByUser);


//checking if ans option id or ques id actually exists even or not
if ($answerOptionIdByUser<=0 || $questionIdByUser<=0 || $answerOptionCurrent->getId()<=0 || $questionCurrent->getId()<=0) //this is checking whether given ids by user are positive or not 
//BUT SHOULDNT WE ALSO CHECK WHETHER THE VALUES WE R LOADING ARE ALSO POSITIVE SO CHECK THEM TOOOO
    {
        http_response_code(400); //not found errors
        echo json_encode([
            'error'=>"Failed to fetch question/answer option!!"
        ]);
        exit;
    }

//session variable might be string now so need to mention int 
//explicit better than implicit
//whether session thing matches what we got here as post
if ((int)$_SESSION['questionIdCurrent']!==$questionIdByUser)
    {
        http_response_code(400); //not found errors
        echo json_encode([
            'error'=>"SERVER STATE AND USER INPUT STATE DOESN'T MATCH!!"
        ]);
        exit;
    }


//first check whether that answer option id matches with question id
$isAnswerOptionIdCorrect=true; //do we really need this, idts rn but maybe later lets see
$answerOptionQuestionIdCorrect=$answerOptionCurrent->getQuestionId();
if ($answerOptionQuestionIdCorrect!==$questionIdByUser)
    {
        $isAnswerOptionIdCorrect=false; //will check the need of it coz error can be given directly right
        http_response_code(500);
        echo json_encode([
            'error'=>"Answer option id and question id doesn't match!!"
        ]);
        exit; ///////very very impppppppp how can u forgettttt
        //execution flow should stop hereeee
    }


//now check whether answer is actually correct for the question or not
$isAnswerOptionCorrectForQuestion=false;
if ($quiz->isAnswerOptionCorrect($answerOptionCurrent, $questionCurrent)===true) //////?????????
    {
        $isAnswerOptionCorrectForQuestion=true;
        $_SESSION['score']++;

    }

$_SESSION['questionsDone']++;



$questionIdNext=$quiz->getQuestionIdNext($questionCurrent);

if ($questionIdNext===0)
    {
        //that means quiz has ended, all questions are done
        echo json_encode([
            'score'=> $_SESSION['score'],
            'questionsDone'=> $_SESSION['questionsDone'],
            'questionCountTotal'=> count($quiz->getQuestions()),
            'isAnswerOptionCorrectForQuestion'=>$isAnswerOptionCorrectForQuestion,
            'isAnswerOptionIdCorrect'=>$isAnswerOptionIdCorrect,
            'isQuizDone'=>true,
            'resultLink'=>'quizResultShow.php'
        ]);
        exit;
    }


//also need to update the session id for next quesiton
$_SESSION['questionIdCurrent']=$questionIdNext;

//else quiz hasnt ended yet
echo json_encode([
    'score'=> $_SESSION['score'],
    'questionsDone'=> $_SESSION['questionsDone'],
    'questionCountTotal'=> count($quiz->getQuestions()),
    'isAnswerOptionCorrectForQuestion'=>$isAnswerOptionCorrectForQuestion,
    'isAnswerOptionIdCorrect'=>$isAnswerOptionIdCorrect,
    'isQuizDone'=>false,
    'questionIdNext'=>$questionIdNext
]);
?>