<?php

declare(strict_types=1);

require_once 'config.php';
require_once 'question.php';

class AnswerOption{
    private int $id; //Id of the answer option
    private string $text; //Text of answer option
    private string $type; //Type of answer option
    private int $questionId; //Id of the question for which this option is the correct answer
    private bool $isCorrect;

    //NOT TO WRITE SUCH COMMENTS, BUT HERE JUST FOR CLARITY AND UNDERSTANDING!!!!!!

    //No answerId/answerText/answerType coz it will automatically be added when calling becoz of class


    //CONSTRUCTOR BASED ON BSTD1 v3.1 Section 8.4
    public function __construct(int $id, string $text, string $type, int $questionId, bool $isCorrect)
    {
        $this->id=$id;
        $this->text=$text;
        $this->type=$type;
        $this->questionId=$questionId;
        $this->isCorrect=$isCorrect;
    }


    public function isAnswerOptionValid (AnswerOption $answerOption) : bool
    {
        if ($answerOption->id<=0)
            {
                return false;
            }

        if ($answerOption->text==="")
            {
                return false;
            }
        if ($answerOption->type==="")
            {
                return false;
            }
        if ($answerOption->questionId<=0)
            {
                return false;
            }
        else{
            return true;
        }

    }


    //GETTERS

    public function getId () : int
    {
        return $this->id;
    }

    public function getType () : string
    {
        return $this->type;
    }

    public function getText () : string
    {
        return $this->text;
    }

    public function getQuestionId () : int
    {
        return $this->questionId;
    }

    public function getIsCorrect () : bool
    {
        return $this->isCorrect;
    }


}


?>