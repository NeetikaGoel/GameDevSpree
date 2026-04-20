<?php
declare(strict_types=1);

require_once 'config.php';
require_once 'question.php';
require_once 'answerOption.php';
require_once 'quizData.php';

class Quiz
{
    private array $questions;
    private array $answerOptions;

    public function __construct()
    {
        $quizData = new QuizData();
        $this->questions = $quizData->getQuestions();
        $this->answerOptions = $quizData->getAnswerOptions();
    }

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function getAnswerOptions(): array
    {
        return $this->answerOptions;
    }

    //Now function for find correct answer options for each question
    public function getAnswerOptionsForQuestion(Question $question): array
    {
        $answerOptionsForQuestion = [];

        foreach ($this->answerOptions as $answerOption) 
        {
            if ($answerOption->getQuestionId()===$question->getId()) 
            {
                $answerOptionsForQuestion[]=$answerOption;
            }
        }
        return $answerOptionsForQuestion;
    }


    //Now next function for after current question, we need next question
    public function getQuestionIdNext(Question $questionCurrent) : int
    {
        $questionIdCurrent=$questionCurrent->getId();

        $questionIdNext=$questionIdCurrent+1;

        //now checking whether its the correct id or not
        if ($questionIdNext<=QUESTION_COUNT_TOTAL)
            {
                return $questionIdNext;
            }
        else return 0; //we will know questions are done
    }

    //to bring question with just an id
    public function getQuestionFromId(int $questionId) : Question
    {
        foreach ($this->questions as $question) 
        {
            if ($question->getId()===$questionId) 
            {
                return $question;
            }
        }

        return new Question(0,'',''); //since we found no question with that id so create a new empty/null question
    }


    //this function for the case when we want first question to be displayed i.e. just when the user selects start quiz
    public function getQuestionFirst() : Question
    {
        return $this->questions[0];
    }

    public function getAnswerOptionFromId(int $answerOptionId) : AnswerOption
    {
        foreach ($this->answerOptions as $answerOption) 
        {
            //THIS IS NOT A MEMORY LEAK COZ MEMORY IS REUSED AND WILL BE DISCARDED AFTER FUNCTION CALL
            $answerOptionIdCurrent=$answerOption->getId();
            if ($answerOptionIdCurrent===$answerOptionId) 
            {
                return $answerOption;
            }
        }

    
        //$a=new AnswerOption(0,'','',0,false); //MEMORY LEAK SCENARIO
        //in case it was not required later then put delete a
        
        return new AnswerOption(0,'','',0,false);
    }


    //to return if answer chosen is correct since if correct then increase score right
    public function isAnswerOptionCorrect(AnswerOption $answerOption, Question $question) : bool
    {
        if ($answerOption->getQuestionId()===$question->getId() && $answerOption->getIsCorrect()===true)
            {
                return true;
            }
        else return false;
    }

}