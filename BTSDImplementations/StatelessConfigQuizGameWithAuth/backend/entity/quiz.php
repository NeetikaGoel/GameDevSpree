<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../entity/question.php';
require_once __DIR__ . '/../entity/answerOption.php';

require_once __DIR__ . '/../../database/repository/questionRepository.php';
require_once __DIR__ . '/../../database/repository/answerOptionRepository.php';

class Quiz
{
    //This class will represent the quiz that will be shown to the user, it will contain the questions and answer options for the quiz and also some helper functions to get the questions and answer options in the desired format for the frontend and also to check if the answer option selected by the user is correct or not
    private array $questions;
    private array $answerOptions;

    public function __construct(array $questionIdListSelected) //we will pass the question id list selected for the quiz and then we will get the questions and answer options for those question ids and store them in the quiz object
{
    $questionIdListSelected=array_map('intval',$questionIdListSelected); //all values in ques id list selected should be in int format
    $questionCountTarget=count($questionIdListSelected); //how many questions we want to show for that quiz

    //initialize new ques repo that will bring ques from db through orm
    $questionRepository=new QuestionRepository();
    $this->questions=$questionRepository->getQuestionsFromQuestionIdListAllowed(
        $questionIdListSelected,
        $questionCountTarget
    );

    //initialize new ans option repo that will bring ques from db through orm
    $answerOptionRepository=new AnswerOptionRepository();
    $this->answerOptions=$answerOptionRepository->getAnswerOptionsFromQuestionIdList($questionIdListSelected);
}


    public function getQuestions():array
    {
        return $this->questions;
    }

    public function getAnswerOptions():array
    {
        return $this->answerOptions;
    }

    public function getAnswerOptionsForQuestion(Question $question):array
    {
        $answerOptionsForQuestion=[];

        foreach ($this->answerOptions as $answerOption)
            {
                if ($answerOption->getQuestionId()===$question->getId())
                    {
                        $answerOptionsForQuestion[]=$answerOption;
                    }
            }

        return $answerOptionsForQuestion;
    }

    public function getQuestionIdOrdering():array
    {
        $questionIdOrder=[];

        foreach ($this->questions as $question)
            {
                $questionIdOrder[]=$question->getId();
            }

        return $questionIdOrder;
    }

    public function getQuestionFromId(int $questionId):Question
    {
        foreach ($this->questions as $question)
            {
                if ($question->getId()===$questionId)
                    {
                        return $question;
                    }
            }

        return new Question(0,'','');
    }

    public function getQuestionFirst():Question
    {
        if (!isset($this->questions[0]))
            {
                return new Question(0,'','');
            }

        return $this->questions[0];
    }

    public function getAnswerOptionFromId(int $answerOptionId):AnswerOption
    {
        foreach ($this->answerOptions as $answerOption)
            {
                if ($answerOption->getId()===$answerOptionId)
                    {
                        return $answerOption;
                    }
            }

        return new AnswerOption(0,'','',0,false);
    }

    public function isAnswerOptionCorrect(AnswerOption $answerOption,Question $question):bool
    {
        if ($answerOption->getQuestionId()===$question->getId() && $answerOption->getIsCorrect()===true)
            {
                return true;
            }

        return false;
    }
}
?>