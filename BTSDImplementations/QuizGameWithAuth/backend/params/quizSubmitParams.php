<?php

declare(strict_types=1);



class QuizSubmitParams
{
    public int $uid;
    public int $answerOptionId;
    public ?int $gameConfigId;

    public function __construct(int $uid, int $gameConfigId, int $answerOptionId)
    {
        $this->uid=$uid;
        $this->answerOptionId=$answerOptionId;
        $this->gameConfigId = $gameConfigId;
    }

    public function getUid():int
    {
        return $this->uid;
    }

    public function getAnswerOptionId():int
    {
        return $this->answerOptionId;
    }

    public function getGameConfigId(): ?int
    {
        return $this->gameConfigId;
    }
}