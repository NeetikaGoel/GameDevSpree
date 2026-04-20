<?php

declare(strict_types=1);



class QuizSubmitParams
{
    public int $quizAttemptId;
    public int $answerOptionId;

    public function __construct(int $quizAttemptId, int $answerOptionId)
    {
        $this->quizAttemptId = $quizAttemptId;
        $this->answerOptionId = $answerOptionId;
    }
}