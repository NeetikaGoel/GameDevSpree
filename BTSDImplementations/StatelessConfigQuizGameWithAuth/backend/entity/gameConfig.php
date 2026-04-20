<?php
declare(strict_types=1);

class GameConfig
{
    private int $id; //Id of the game config
    private string $gameConfigName; //Name of the game config like "gk" or "festivals focused"
    private int $questionCountTarget; //Number of questions that we want to show in the quiz for this game config    
    private array $questionIdListAllowed; //List of question ids that we want to allow for this game config, if this list is empty then it means that we want to allow all the questions for this game config

    public function __construct(int $id,string $gameConfigName,int $questionCountTarget,array $questionIdListAllowed)
    {
        $this->id=$id;
        $this->gameConfigName=$gameConfigName;
        $this->questionCountTarget=$questionCountTarget;
        $this->questionIdListAllowed=$questionIdListAllowed;
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
}