<?php

declare(strict_types=1);



class QuizLoadParams
{
    public ?int $quizAttemptId;

    public function __construct(?int $quizAttemptId)
    {
        $this->quizAttemptId = $quizAttemptId;
    }
}