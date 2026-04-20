<?php

declare(strict_types=1);

class QuizResultShowParams
{
    public int $quizAttemptId;

    public function __construct(int $quizAttemptId)
    {
        $this->quizAttemptId = $quizAttemptId;
    }
}