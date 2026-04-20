<?php
declare(strict_types=1);

require_once __DIR__ . '/../ormManager.php';
require_once __DIR__ . '/../query/questionQuery.php';
require_once __DIR__ . '/../mapper/questionMapper.php';

class QuestionRepository
{
    public function getQuestions(): array
    {
        $questionQuery=new QuestionQuery();
        $questionMapper=new QuestionMapper();
        $ormManager=new OrmManager();

        $sql=$questionQuery->getSqlQuery();

        return $ormManager->ormManage($sql,$questionMapper);
    }
}