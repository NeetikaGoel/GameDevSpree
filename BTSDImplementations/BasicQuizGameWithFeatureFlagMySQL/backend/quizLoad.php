<?php
declare(strict_types=1);

session_start();


require_once 'config.php';
require_once 'question.php';
require_once 'answerOption.php';
require_once 'quizData.php';
require_once 'quiz.php';

////////ADDITION OF NEW FILE NOW DUE TO FEATURE FLAG

require_once 'featureFlag.php';


//since we r gonna try in terminal mode testing first 
// - so for that mentioning this line is imp
header('Content-Type: application/json');



//so now finally initialize quizzzz
$quiz=new Quiz(); //will be in heap memory now
// $quiz->getQuestionFirst(); //very wrong

/** 
SO LETS FIRST START WITH VARIABLES INITIALIZE
NEW USER-NEW LOAD
WILL START WITH 1ST QUESTION NAD SCORE=0 AND QUESIONS COUNT 1/5

SO UPDATING SUCH VARIABLES OF SESSION

so if score not there, make it zero first-MOST IMP
*/


/**
 SESSION VARIABLES WE R CONSIDERING
 1) SCORE
 2) QUESTIONS DONE
 3) QUESTION ID CURRENT

 */

if (!isset($_SESSION['score']))
    {
        $_SESSION['score']=0;   //initialize score as 0 first if there is no prev score 
    }


if (!isset($_SESSION['questionsDone']))
    {
        $_SESSION['questionsDone']=0; //initilize ques done as 0 too if just started with quiz
    }


//if all quesetions done then move to result page, dont just stay here
if ($_SESSION['questionsDone']>=count($quiz->getQuestions())) 
    {
        http_response_code(200);
        echo json_encode([
            'isQuizDone'=>true,
            'resultLink'=>'quizResultShow.php'
        ]);
        exit;
    }

/**
    #####################################################################################
    #####################################################################################
    */
    
//WE NEED MORE VARIABLES NOW COZ QUESITON ID CURRENT IS NOT RIGHT NOW AFTER THE FEATURE FLAG

if ((!isset($_SESSION['questionIdOrder']) || !isset($_SESSION['questionIdOrderIndexCurrent'])))
    {
        $questionIdOrder=$quiz->getQuestionIdOrdering();

        if (getFeatureFlag()==true)
            {
                shuffle($questionIdOrder);
            }

        $_SESSION['questionIdOrder']=$questionIdOrder;
        $_SESSION['questionIdOrderIndexCurrent']=0;
    }




/**
    #####################################################################################
    #####################################################################################
    */
    


/**
NOW VARIABLES SET DONE
NOW QUESTION SHOW
BUT WHICH QUESTION GOOD QUESTION
2 OPTIONS
1ST IS 1ST QUESITON SHOW IF USER ENTEREED FIRST TIME
2ND COULD BE WHEN NOT FIRST TIME BUT LOADING NEXT QUESTION

WE HAVE ALREADY FUNCTION FOR RETURN FIRST QUESTION AND ALSO GET QUESTION BY ID
BUT SINCE HOW DO WE KNOW ID
MAYBE WE CAN ADD ITS VALUE SOMEWHERE TO SHOW WHETHERE FIRST QUES OR NOT FIRST

LETS TAKE FOR EG ITS ALSO SESSION VARIABLE HERE 
*/

// if (!isset($_SESSION['questionIdCurrent']))
//     {
//         //is there any id already there or have to send the 1st question now
//         //since its not so load the first question alreadyyy
//         $questionCurrent=$quiz->getQuestionFirst(); //1st question
//         $_SESSION['questionIdCurrent']=$questionCurrent->getId();

//     }
// else{
//     //now if it set already so we were already on some prev ques
//     $questionCurrent=$quiz->getQuestionFromId( (int)$_SESSION['questionIdCurrent']); //int or not. //explicitly say int girl
// }



//NOW NEW CODE FOR THE FEATURE FLAG
$questionIdOrder=$_SESSION['questionIdOrder'];
$questionIdOrderIndexCurrent=(int)$_SESSION['questionIdOrderIndexCurrent'];

if (!isset($questionIdOrder[$questionIdOrderIndexCurrent]))
    {
        http_response_code(500);
        echo json_encode([
            'error'=>"Failed to load the current question becoz Invalid index!!"
        ]);
        exit;
    }


$questionIdCurrent=(int)$questionIdOrder[$questionIdOrderIndexCurrent];
$_SESSION['questionIdCurrent']=$questionIdCurrent;
$questionCurrent=$quiz->getQuestionFromId($questionIdCurrent);


if ($questionCurrent->getId()<=0)
    {
        //it just means that no valid question has been retrieved
        //so we will load error 
        http_response_code(500);
        echo json_encode([
            'error'=>"Failed to load the current question!!"
        ]);
        exit;

    }

//now if question actually is correct and we have no error
//so load the ANSWER OPTIONS NOWWWWWW
$answerOptionsForQuestionCurrent=$quiz->getAnswerOptionsForQuestion($questionCurrent); ///????

//now save all id, type, text of all answerOptions separately for each answerOption

$answerOptionsForQuestionCurrentDetail=[];
foreach ($answerOptionsForQuestionCurrent as $answerOptionForQuestionCurrentTemp)
    {
        $answerOptionsForQuestionCurrentDetail[]=
        [
            'id'=> $answerOptionForQuestionCurrentTemp->getId(),
            'type'=> $answerOptionForQuestionCurrentTemp->getType(),
            'text'=> $answerOptionForQuestionCurrentTemp->getText()
        ];
    };

/**
SO THIS PAGE WILL SEND DATA TO NEXT FILE WHICH IS QUIZSUBMIT.PHP
*/

/**
 NOW MAKING IT A SMALL API, IT SHOULD RETURN DATA IN JSON FORMAT

 */


echo json_encode([
    'score'=> $_SESSION['score'],
    'questionsDone'=> $_SESSION['questionsDone'],
    'questionIdCurrent'=> $questionCurrent->getId(),
    'questionTextCurrent'=> $questionCurrent->getText(),
    'questionTypeCurrent'=> $questionCurrent->getType(),
    'answerOptionsCurrent'=> $answerOptionsForQuestionCurrentDetail,   ///????
    'questionCountTotal'=> count($quiz->getQuestions())

]);



?>