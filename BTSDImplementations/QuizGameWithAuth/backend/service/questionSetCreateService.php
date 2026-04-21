<?php

declare(strict_types=1);

//ADD IMPORTS FIRST
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logging.php';

require_once __DIR__ . '/../../database/repository/gameConfigRepository.php';
require_once __DIR__ . '/../../database/repository/questionRepository.php';

class QuestionSetCreateService
{
    //WE WILL COME HERE AFTER SELECTING QUESTIONS!!!!!!
    public function questionSetCreateService(string $gameConfigName, array $questionIdListAllowed, bool $makeActive): array
    {
        $gameConfigRepository = new GameConfigRepository();
        $questionRepository = new QuestionRepository();

        $gameConfigCurrent = $gameConfigRepository->getGameConfigFromName($gameConfigName);

        if ($gameConfigCurrent !== null) {
            throw new InvalidArgumentException('A question set with this name already exists!!');
        }

        if (count($questionIdListAllowed) === 0) {
            throw new InvalidArgumentException('Question id list allowed cannot be empty!!');
        }

        $questionIdListAllowedSanitized = [];
        $questionIdListAllowedSeen = [];

        foreach ($questionIdListAllowed as $questionIdCurrent) {
            $questionIdCurrent = (int)$questionIdCurrent;

            if ($questionIdCurrent <= 0) {
                throw new InvalidArgumentException('Each question id must be a positive integer!!');
            }

            if (!isset($questionIdListAllowedSeen[$questionIdCurrent])) {
                $questionIdListAllowedSeen[$questionIdCurrent] = true;
                $questionIdListAllowedSanitized[] = $questionIdCurrent;
            }
        }

        $questionCountTarget = count($questionIdListAllowedSanitized);

        if ($questionCountTarget <= 0) {
            throw new InvalidArgumentException('Question count target must be positive!!');
        }

        $questionListCurrent = $questionRepository->getQuestionsFromQuestionIdListAllowed(
            $questionIdListAllowedSanitized,
            $questionCountTarget
        );

        if (count($questionListCurrent) !== count($questionIdListAllowedSanitized)) {
            throw new InvalidArgumentException('One or more question ids do not exist in database!!');
        }

        $secretKey = '';

        $activeGameConfigCurrent = $gameConfigRepository->getActiveGameConfig();

        if ($activeGameConfigCurrent !== null && $activeGameConfigCurrent->getSecretKey() !== '') {
            $secretKey = $activeGameConfigCurrent->getSecretKey();
        } else {
            $defaultGameConfigCurrent = $gameConfigRepository->getGameConfigFromName(GAME_CONFIG_NAME_DEFAULT);

            if ($defaultGameConfigCurrent !== null && $defaultGameConfigCurrent->getSecretKey() !== '') {
                $secretKey = $defaultGameConfigCurrent->getSecretKey();
            }
        }

        if ($secretKey === '') {
            throw new RuntimeException('Secret key could not be resolved from existing configs!!');
        }

        $gameConfigId = $gameConfigRepository->createGameConfig(
            $gameConfigName,
            $questionCountTarget,
            $questionIdListAllowedSanitized,
            $secretKey,
            false
        );

        if ($gameConfigId <= 0) {
            throw new RuntimeException('Question set creation failed!!');
        }

        $isActive = false;

        if ($makeActive === true) {
            $gameConfigRepository->deactivateAllGameConfigs();
            $gameConfigRepository->activateGameConfigFromId($gameConfigId);
            $isActive = true;
        }

        Logger::logInfo(
            'questionSetCreateService',
            'Question set create completed successfully!!',
            [
                'gameConfigId' => $gameConfigId,
                'gameConfigName' => $gameConfigName,
                'isActive' => $isActive
            ]
        );

        return [
            'gameConfigId' => $gameConfigId,
            'gameConfigName' => $gameConfigName,
            'questionCountTarget' => $questionCountTarget,
            'questionIdListAllowed' => $questionIdListAllowedSanitized,
            'isActive' => $isActive,
            'isCreated' => true
        ];
    }
}
