<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logging.php';

require_once __DIR__ . '/../../database/repository/gameConfigRepository.php';
require_once __DIR__ . '/../../database/repository/questionRepository.php';

class QuestionSetEditService
{
    //WE WILL COME AFTER SELECTING QUESTION SET HERE HEHE
    public function questionSetEditService(int $gameConfigId,string $gameConfigName,array $questionIdListAllowed,bool $makeActive): array
    {
        $gameConfigRepository=new GameConfigRepository();
        $questionRepository=new QuestionRepository();

        $gameConfigCurrent=$gameConfigRepository->getGameConfigFromId($gameConfigId);

        if ($gameConfigCurrent===null) 
        {
            throw new InvalidArgumentException('Question set with this id was not found!!');
        }

        $gameConfigExistingFromName=$gameConfigRepository->getGameConfigFromName($gameConfigName);

        if ($gameConfigExistingFromName!==null && $gameConfigExistingFromName->getId()!==$gameConfigId) 
        {
            throw new InvalidArgumentException('Another question set with this name already exists!!');
        }

        if (count($questionIdListAllowed)===0) 
        {
            throw new InvalidArgumentException('Question id list allowed cannot be empty!!');
        }

        $questionIdListAllowedSanitized=[];
        $questionIdListAllowedSeen=[];

        foreach ($questionIdListAllowed as $questionIdCurrent) 
        {
            $questionIdCurrent=(int)$questionIdCurrent;

            if (!isset($questionIdListAllowedSeen[$questionIdCurrent])) 
            {
                $questionIdListAllowedSeen[$questionIdCurrent]=true;
                $questionIdListAllowedSanitized[]=$questionIdCurrent;
            }
        }

        $questionCountTarget=count($questionIdListAllowedSanitized);

        if ($questionCountTarget<=0) 
        {
            throw new InvalidArgumentException('Question count target must be positive!!');
        }

        $questionListCurrent=$questionRepository->getQuestionsFromQuestionIdListAllowed(
            $questionIdListAllowedSanitized,
            $questionCountTarget
        );

        if (count($questionListCurrent)!==count($questionIdListAllowedSanitized)) 
        {
            throw new InvalidArgumentException('One or more question ids do not exist in database!!');
        }

        $finalIsActive=$gameConfigCurrent->getIsActive();

        if ($makeActive===true) 
        {
            $gameConfigRepository->deactivateAllGameConfigs();
            $finalIsActive=true;
        }

        $gameConfigRepository->updateGameConfigFromId(
            $gameConfigId,
            $gameConfigName,
            $questionCountTarget,
            $questionIdListAllowedSanitized,
            $finalIsActive
        );

        Logger::logInfo(
            'questionSetEditService',
            'Question set edit completed successfully!!',
            [
                'gameConfigId'=>$gameConfigId,
                'gameConfigName'=>$gameConfigName,
                'isActive'=>$finalIsActive
            ]
        );

        return [
            'gameConfigId'=>$gameConfigId,
            'gameConfigName'=>$gameConfigName,
            'questionCountTarget'=>$questionCountTarget,
            'questionIdListAllowed'=>$questionIdListAllowedSanitized,
            'isActive'=>$finalIsActive,
            'isUpdated'=>true
        ];
    }
}
