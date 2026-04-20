<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logging.php';

require_once __DIR__ . '/../../database/repository/gameConfigRepository.php';
require_once __DIR__ . '/../../database/repository/questionRepository.php';


class QuestionSetEditService
{
    public function questionSetEditService(string $gameConfigName,int $questionCountTarget,array $questionIdListAllowed,string $secretKey):array
    {
        $gameConfigRepository=new GameConfigRepository();
        $questionRepository=new QuestionRepository();

        $gameConfigCurrent=$gameConfigRepository->getGameConfigFromName($gameConfigName);

        if ($gameConfigCurrent===null)
            {
                throw new InvalidArgumentException('Question set with this name was not found!!');
            }

        if ($questionCountTarget<=0)
            {
                throw new InvalidArgumentException('Question count target must be positive!!');
            }

        if (count($questionIdListAllowed)===0)
            {
                throw new InvalidArgumentException('Question id list allowed cannot be empty!!');
            }

        if ($secretKey==='')
            {
                throw new InvalidArgumentException('Secret key cannot be empty!!');
            }

        $questionIdListAllowedSanitized=[];
        $questionIdListAllowedSeen=[];

        foreach ($questionIdListAllowed as $questionIdCurrent)
            {
                $questionIdCurrent=(int)$questionIdCurrent;

                if ($questionIdCurrent<=0)
                    {
                        throw new InvalidArgumentException('Each question id must be a positive integer!!');
                    }

                if (!isset($questionIdListAllowedSeen[$questionIdCurrent]))
                    {
                        $questionIdListAllowedSeen[$questionIdCurrent]=true;
                        $questionIdListAllowedSanitized[]=$questionIdCurrent;
                    }
            }

        if ($questionCountTarget>count($questionIdListAllowedSanitized))
            {
                throw new InvalidArgumentException('Question count target cannot be more than allowed question ids count!!');
            }

        $questionListCurrent=$questionRepository->getQuestionsFromQuestionIdListAllowed(
            $questionIdListAllowedSanitized,
            count($questionIdListAllowedSanitized)
        );

        if (count($questionListCurrent)!==count($questionIdListAllowedSanitized))
            {
                throw new InvalidArgumentException('One or more question ids do not exist in database!!');
            }

        $gameConfigRepository->updateGameConfigFromName(
            $gameConfigName,
            $questionCountTarget,
            $questionIdListAllowedSanitized,
            $secretKey
        );

        Logger::logInfo(
            'questionSetEditService',
            'Question set edit completed successfully!!',
            [
                'gameConfigName'=>$gameConfigName
            ]
        );

        return [
            'gameConfigName'=>$gameConfigName,
            'questionCountTarget'=>$questionCountTarget,
            'questionIdListAllowed'=>$questionIdListAllowedSanitized,
            'secretKey'=>$secretKey,
            'isUpdated'=>true
        ];
    }
}
?>