<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

class UserProgressState
{
    private int $id;
    private int $uid;
    private int $scoreCurrent;
    private int $questionsDone;
    private array $questionIdOrder;
    private int $questionIdOrderIndexCurrent;
    private int $questionIdCurrent;
    private bool $isComplete;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(int $id,int $uid,int $scoreCurrent,int $questionsDone,array $questionIdOrder,int $questionIdOrderIndexCurrent,int $questionIdCurrent,bool $isComplete,string $createdAt,string $updatedAt)
    {
        $this->id=$id;
        $this->uid=$uid;
        $this->scoreCurrent=$scoreCurrent;
        $this->questionsDone=$questionsDone;
        $this->questionIdOrder=$questionIdOrder;
        $this->questionIdOrderIndexCurrent=$questionIdOrderIndexCurrent;
        $this->questionIdCurrent=$questionIdCurrent;
        $this->isComplete=$isComplete;
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

    public function getScoreCurrent():int
    {
        return $this->scoreCurrent;
    }

    public function getQuestionsDone():int
    {
        return $this->questionsDone;
    }

    public function getQuestionIdOrder():array
    {
        return $this->questionIdOrder;
    }

    public function getQuestionIdOrderIndexCurrent():int
    {
        return $this->questionIdOrderIndexCurrent;
    }

    public function getQuestionIdCurrent():int
    {
        return $this->questionIdCurrent;
    }

    public function getIsComplete():bool
    {
        return $this->isComplete;
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
?>