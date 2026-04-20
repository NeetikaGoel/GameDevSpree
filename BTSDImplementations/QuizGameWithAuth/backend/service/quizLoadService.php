<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../entity/question.php';
require_once __DIR__ . '/../entity/answerOption.php';
require_once __DIR__ . '/../entity/gameConfig.php';
require_once __DIR__ . '/../entity/quiz.php';
require_once __DIR__ . '/../entity/featureFlag.php';

require_once __DIR__ . '/logging.php';
require_once __DIR__ . '/../../database/repository/userProgressStateRepository.php';
require_once __DIR__ . '/../../database/repository/gameConfigRepository.php';

//ALL BUSINESS LOGIC OF QUIZ LOAD FILE HERE 


//now what all it will do::::
// 1. create new quiz attempt if needed
// 2. get quiz attempt from DB
// 3. check if attempt is complete
// 4. read current question order
// 5. get current question
// 6. get answer options
// 7. build response data

//business logic of quizLoad api here
class QuizLoadService
{
    //take quiz attempt id and return data that we need to show the quiz question and answer options and also quiz attempt id for future calls and also if quiz is done or not and if done then link to result page
    public function quizLoadService(int $uid):array
    {
        //initialize quiz attempt repo to interact with quiz attempt table in db through orm
        // $quizAttemptRepository=new QuizAttemptRepository();

        $userProgressStateRepository=new UserProgressStateRepository();



        $userProgressState=$userProgressStateRepository->getUserProgressStateFromUid($uid);

        if ($userProgressState===null)
            {
                $questionIdOrder=$this->getQuestionIdOrderFromConfig();

                if (count($questionIdOrder)===0)
                    {
                        throw new RuntimeException('Question id order could not be created!!');
                    }

                $userProgressStateId=$userProgressStateRepository->createUserProgressState($uid,$questionIdOrder);

                if ($userProgressStateId<=0)
                    {
                        throw new RuntimeException('User progress state creation failed!!');
                    }

                $userProgressState=$userProgressStateRepository->getUserProgressStateFromUid($uid);

                if ($userProgressState===null)
                    {
                        throw new RuntimeException('User progress state could not be loaded after creation!!');
                    }
            }



        //now we have quiz attempt id either from request or newly created one, now we will get quiz attempt data from db using that id and then we will get question order and current question index and then we will get current question and answer options and then we will build response data and return it

        
        $questionIdOrder=$userProgressState->getQuestionIdOrder();

        if (!is_array($questionIdOrder))
            {
                throw new RuntimeException('User progress state question order is invalid!!');
            }



        $questionIdOrderIndexCurrent=$userProgressState->getQuestionIdOrderIndexCurrent();
        $questionsDone=$userProgressState->getQuestionsDone();
        $scoreCurrent=$userProgressState->getScoreCurrent();

        if ($questionIdOrderIndexCurrent<0)
            {
                throw new RuntimeException('User progress state current index is invalid!!');
            }

        if ($questionsDone<0 || $scoreCurrent<0)
            {
                throw new RuntimeException('User progress state progress state is invalid!!');
            }

        //if its already complete then go to result page directly 
        if ($userProgressState->getIsComplete()===true)
            {
                return [
                    'uid'=>$uid,
                    'isQuizDone'=>true,
                    'resultLink'=>'quizResultShow.php'
                ];
            }

        //if not correct index for the ques id order throw error
        if (!isset($questionIdOrder[$questionIdOrderIndexCurrent]))
            {
                throw new RuntimeException('Current question index is out of bounds!!');
            }

        //if ques id we get not numeric then also throw error
        if (!is_numeric($questionIdOrder[$questionIdOrderIndexCurrent]))
            {
                throw new RuntimeException('Current question id is invalid!!');
            }

        //intialize ques with teh ques id order that we have got
        $quiz=new Quiz($questionIdOrder);

        $questionIdCurrent=(int)$questionIdOrder[$questionIdOrderIndexCurrent];
        $questionCurrent=$quiz->getQuestionFromId($questionIdCurrent);

        //if we have no question for that ques id
        if ($questionCurrent->getId()<=0)
            {
                throw new RuntimeException('Question with current id was not found!!');
            }
        
        //now bring ans op for the ques current that is being shown to user
        $answerOptionsForQuestionCurrent=$quiz->getAnswerOptionsForQuestion($questionCurrent);

        //if no ans options found wow
        if (count($answerOptionsForQuestionCurrent)===0)
            {
                throw new RuntimeException('No answer options found for current question!!');
            }

        //we need detail of all ans options now
        $answerOptionsForQuestionCurrentDetail=[];

        //loop through all options to get their details and put in array to send to frontend
        foreach ($answerOptionsForQuestionCurrent as $answerOptionForQuestionCurrentTemp)
            {
                $answerOptionsForQuestionCurrentDetail[]=[
                    'id'=>$answerOptionForQuestionCurrentTemp->getId(),
                    'type'=>$answerOptionForQuestionCurrentTemp->getType(),
                    'text'=>$answerOptionForQuestionCurrentTemp->getText()
                ];
            }


        //now return al data to frontend whatever we have
        return [
            'uid'=>$uid,
            'score'=>$scoreCurrent,
            'questionsDone'=>$questionsDone,
            'questionIdCurrent'=>$questionCurrent->getId(),
            'questionTextCurrent'=>$questionCurrent->getText(),
            'questionTypeCurrent'=>$questionCurrent->getType(),
            'answerOptionsCurrent'=>$answerOptionsForQuestionCurrentDetail,
            'questionCountTotal'=>count($questionIdOrder),
            'isQuizDone'=>false
        ];
    }

    //a new function to get ques id order from game config and also apply feature flag for randomization if enabled and also apply question count target to limit the number of questions based on config
    private function getQuestionIdOrderFromConfig():array
    {
        //initialize game config repo to interact with game config table in db through orm
        $gameConfigRepository=new GameConfigRepository();

        //default take
        $questionCountTarget=GAME_CONFIG_QUESTION_COUNT_TARGET_DEFAULT;
        $questionIdListAllowed=GAME_CONFIG_QUESTION_ID_LIST_ALLOWED_DEFAULT;

        //try to get game config from db and if there is some error then we will log that error and then we will use the default values for question count target and question id list allowed can be error in fetching thats why try catch
        try
        {
            $gameConfigCurrent=$gameConfigRepository->getGameConfigFromName(
                GAME_CONFIG_NAME_DEFAULT
            );

            if ($gameConfigCurrent!==null) //if we got game config with that name in db
                {
                    if ($gameConfigCurrent->getQuestionCountTarget()>0) //how many ques we want to show and is it greater than 0
                        {
                            $questionCountTarget=$gameConfigCurrent->getQuestionCountTarget();
                        }

                    if (count($gameConfigCurrent->getQuestionIdListAllowed())>0) //if some specific ques id list is allowed then we will take that otherwise we will take all ques ids
                        {
                            $questionIdListAllowed=$gameConfigCurrent->getQuestionIdListAllowed();
                        }
                }
        }
        //catch coz what if got some error in db
        catch (Throwable $exception)
        {
            Logger::logWarn('QuizLoadService','Falling back to config defaults for game config!!','GAME_CONFIG_FALLBACK',
                [
                    'reason'=>$exception->getMessage()
                ]
            );
        }

        //also see random feature flag is enables or not
        if (getFeatureFlag()===true)
            {
                shuffle($questionIdListAllowed); //if enabled shuffle the questions
            }

        return array_map('intval',array_slice($questionIdListAllowed,0,$questionCountTarget)); //limit the number of questions based on config target and 0 is for starting idx
    }
}
