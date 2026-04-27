<?php

declare(strict_types=1);



class QuizLoadParams
{
    public ?int $uid;
    public ?int $gameConfigId;

    public function __construct(?int $uid, ?int $gameConfigId)
    {
        $this->uid = $uid;
        $this->gameConfigId = $gameConfigId;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function getGameConfigId(): ?int
    {
        return $this->gameConfigId;
    }
}