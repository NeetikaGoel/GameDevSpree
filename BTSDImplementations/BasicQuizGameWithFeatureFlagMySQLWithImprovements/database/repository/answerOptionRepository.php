<?php
declare(strict_types=1);

require_once __DIR__ . '/../ormManager.php';
require_once __DIR__ . '/../query/answerOptionQuery.php';
require_once __DIR__ . '/../mapper/answerOptionMapper.php';

class AnswerOptionRepository
{
    public function getAnswerOptions(): array
    {
        $answerOptionQuery=new AnswerOptionQuery();
        $answerOptionMapper=new AnswerOptionMapper();
        $ormManager=new OrmManager();

        $sql=$answerOptionQuery->getSqlQuery();

        return $ormManager->ormManage($sql,$answerOptionMapper);
    }
}