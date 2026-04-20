<?php

declare(strict_types=1);



class QuizSubmitParams
{
    public int $uid;
    public int $answerOptionId;

    public function __construct(int $uid, int $answerOptionId)
    {
        $this->uid=$uid;
        $this->answerOptionId=$answerOptionId;
    }

    public function getUid():int
    {
        return $this->uid;
    }

    public function getAnswerOptionId():int
    {
        return $this->answerOptionId;
    }
}