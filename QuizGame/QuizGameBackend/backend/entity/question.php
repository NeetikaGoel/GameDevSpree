<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

class Question{
    private int $id; //Id of the question
    private string $text; //Text of question
    private string $type; //Type of question

    //NOT TO WRITE SUCH COMMENTS, BUT HERE JUST FOR CLARITY AND UNDERSTANDING!!!!!!

    //No questionId/questionText/questionType coz it will automatically be added when calling becoz of class


    //CONSTRUCTOR BASED ON BSTD1 v3.1 Section 8.4
    public function __construct(int $id, string $text, string $type)
    {
        $this->id=$id;
        $this->text=$text;
        $this->type=$type;
    }


    public function isQuestionValid (Question $question):bool
    {
        if ($question->id<=0)
            {
                return false;
            }

        if ($question->text==="")
            {
                return false;
            }
        if ($question->type==="")
            {
                return false;
            }
        else{
            return true;
        }

    }


    public function getId():int
    {
        return $this->id;
    }

    public function getType():string
    {
        return $this->type;
    }

    public function getText():string
    {
        return $this->text;
    }
}


?>