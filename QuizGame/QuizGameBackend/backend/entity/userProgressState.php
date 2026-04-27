<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

class UserProgressState
{
    private int $id;
    private int $uid;
    private int $gameConfigId;
    private int $scoreCurrent;
    private int $scoreHighest;
    private int $playCount;
    private array $questionIdOrder;
    private int $questionIdOrderIndexCurrent;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(int $id, int $uid, int $gameConfigId, int $scoreCurrent, int $scoreHighest, int $playCount, array $questionIdOrder, int $questionIdOrderIndexCurrent, string $createdAt, string $updatedAt)
    {
        $this->id=$id;
        $this->uid=$uid;
        $this->gameConfigId=$gameConfigId;
        $this->scoreCurrent=$scoreCurrent;
        $this->scoreHighest=$scoreHighest;
        $this->playCount=$playCount;
        $this->questionIdOrder=$questionIdOrder;
        $this->questionIdOrderIndexCurrent=$questionIdOrderIndexCurrent;
        $this->createdAt=$createdAt;
        $this->updatedAt=$updatedAt;
    }


    //now getters
    public function getId():int
    {
        return $this->id;
    }

    public function getUid():int
    {
        return $this->uid;
    }

    public function getGameConfigId():int
    {
        return $this->gameConfigId;
    }

    public function getScoreCurrent():int
    {
        return $this->scoreCurrent;
    }

    public function getScoreHighest():int
    {
        return $this->scoreHighest;
    }

    public function getPlayCount():int
    {
        return $this->playCount;
    }

    public function getQuestionIdOrder():array
    {
        return $this->questionIdOrder;
    }

    public function getQuestionIdOrderIndexCurrent():int
    {
        return $this->questionIdOrderIndexCurrent;
    }

    public function getCreatedAt():string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt():string
    {
        return $this->updatedAt;
    }
}
