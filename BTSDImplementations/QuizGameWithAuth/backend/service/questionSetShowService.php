<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logging.php';

require_once __DIR__ . '/../../database/repository/gameConfigRepository.php';

class QuestionSetShowService
{
    public function questionSetShowService(int $cursor, int $limit): array
    {
        $gameConfigRepository = new GameConfigRepository();

        $gameConfigListCurrent = $gameConfigRepository->getGameConfigsPageAfterId($cursor, $limit + 1);

        $hasMore = false;
        $nextCursor = null;

        if (count($gameConfigListCurrent) > $limit) {
            $hasMore = true;
            $gameConfigListCurrent = array_slice($gameConfigListCurrent, 0, $limit);
        }

        $gameConfigsResponse = [];

        foreach ($gameConfigListCurrent as $gameConfigCurrent) {
            $gameConfigsResponse[] = [
                'id' => $gameConfigCurrent->getId(),
                'gameConfigName' => $gameConfigCurrent->getGameConfigName(),
                'questionCountTarget' => $gameConfigCurrent->getQuestionCountTarget(),
                'questionIdListAllowed' => $gameConfigCurrent->getQuestionIdListAllowed(),
                'isActive' => $gameConfigCurrent->getIsActive(),
                'createdAt' => $gameConfigCurrent->getCreatedAt(),
                'updatedAt' => $gameConfigCurrent->getUpdatedAt()
            ];
        }

        if (count($gameConfigsResponse) > 0) {
            $lastGameConfigCurrent = $gameConfigsResponse[count($gameConfigsResponse) - 1];
            $nextCursor = (int)$lastGameConfigCurrent['id'];
        }

        Logger::logInfo(
            'questionSetShowService',
            'Question set show completed successfully!!',
            [
                'cursor' => $cursor,
                'limit' => $limit,
                'returnedGameConfigCount' => count($gameConfigsResponse),
                'hasMore' => $hasMore
            ]
        );

        return [
            'gameConfigs' => $gameConfigsResponse,
            'nextCursor' => $hasMore ? $nextCursor : null,
            'hasMore' => $hasMore
        ];
    }
}
