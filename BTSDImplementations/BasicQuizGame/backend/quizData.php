<?php
declare(strict_types=1);

require_once 'question.php';
require_once 'answerOption.php';

class QuizData
{
    public function getQuestions(): array
    {
        return 
        [
            new Question(1,'What is 2+2?','mcq'),
            new Question(2,'What is the capital of France?','mcq'),
            new Question(3,'The sun rises in the East!','true/false'),
            new Question(4,'What is 5x3?','mcq'),
            new Question(5,'PHP is a backend language!','true/false'),
        ];
    }

    public function getAnswerOptions(): array
    {
        return [

            new AnswerOption(1,'3','mcq',1,false),
            new AnswerOption(2,'4','mcq',1,true),
            new AnswerOption(3,'5','mcq',1,false),

            new AnswerOption(4,'Berlin','mcq',2,false),
            new AnswerOption(5,'Paris','mcq',2,true),
            new AnswerOption(6,'Rome','mcq',2,false),

            new AnswerOption(7,'True','true/false',3,true),
            new AnswerOption(8,'False','true/false',3,false),

            new AnswerOption(9,'10','mcq',4,false),
            new AnswerOption(10,'15','mcq',4,true),
            new AnswerOption(11,'20','mcq',4,false),

            new AnswerOption(12,'True','true/false',5,true),
            new AnswerOption(13,'False','true/false',5,false),

        ];
    }
}