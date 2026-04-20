<?php
declare(strict_types=1);

class GameConfig
{
    private int $id; //Id of the game config
    private string $gameConfigName; //Name of the game config like "gk" or "festivals focused"
    private int $questionCountTarget; //Number of questions that we want to show in the quiz for this game config    
    private array $questionIdListAllowed; //List of question ids that we want to allow for this game config, if this list is empty then it means that we want to allow all the questions for this game config
    private string $secretKey; //Hidden secret key for internal use only
    private bool $isActive; //Whether this config is the currently active config for quiz load
    private string $createdAt; //When this config was created
    private string $updatedAt; //When this config was last updated

    public function __construct(
        int $id,
        string $gameConfigName,
        int $questionCountTarget,
        array $questionIdListAllowed,
        string $secretKey,
        bool $isActive,
        string $createdAt,
        string $updatedAt
    )
    {
        $this->id=$id;
        $this->gameConfigName=$gameConfigName;
        $this->questionCountTarget=$questionCountTarget;
        $this->questionIdListAllowed=$questionIdListAllowed;
        $this->secretKey=$secretKey;
        $this->isActive=$isActive;
        $this->createdAt=$createdAt;
        $this->updatedAt=$updatedAt;
    }

    public function getId():int
    {
        return $this->id;
    }

    public function getGameConfigName():string
    {
        return $this->gameConfigName;
    }

    public function getQuestionCountTarget():int
    {
        return $this->questionCountTarget;
    }

    public function getQuestionIdListAllowed():array
    {
        return $this->questionIdListAllowed;
    }

    public function getSecretKey():string
    {
        return $this->secretKey;
    }

    public function getIsActive():bool
    {
        return $this->isActive;
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