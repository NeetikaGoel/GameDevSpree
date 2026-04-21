<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logging.php';

require_once __DIR__ . '/../../database/repository/questionRepository.php';
require_once __DIR__ . '/../../database/repository/answerOptionRepository.php';

class QuestionShowService
{
    public function questionShowService(int $cursor,int $limit): array
    {
        $questionRepository=new QuestionRepository();
        $answerOptionRepository=new AnswerOptionRepository();

        $questionListCurrent=$questionRepository->getQuestionPageAfterId($cursor,$limit+1);

        $hasMore=false;
        $nextCursor=null;

        if (count($questionListCurrent) > $limit) {
            $hasMore=true;
            $questionListCurrent=array_slice($questionListCurrent,0,$limit);
        }

        $questionIdList=[];

        foreach ($questionListCurrent as $questionCurrent) 
        {
            $questionIdList[]=$questionCurrent->getId();
        }

        $answerOptionListCurrent=$answerOptionRepository->getAnswerOptionsFromQuestionIdList($questionIdList);

        $answerOptionMap=[];

        foreach ($answerOptionListCurrent as $answerOptionCurrent) 
        {
            $questionIdCurrent=$answerOptionCurrent->getQuestionId();

            if (!isset($answerOptionMap[$questionIdCurrent])) 
            {
                $answerOptionMap[$questionIdCurrent]=[];
            }

            $answerOptionMap[$questionIdCurrent][]=[
                'id'=>$answerOptionCurrent->getId(),
                'text'=>$answerOptionCurrent->getText(),
                'type'=>$answerOptionCurrent->getType()
            ];
        }

        $questionsResponse=[];

        foreach ($questionListCurrent as $questionCurrent) 
        {
            $questionsResponse[]=[
                'questionId'=>$questionCurrent->getId(),
                'questionText'=>$questionCurrent->getText(),
                'questionType'=>$questionCurrent->getType(),
                'answerOptions'=>$answerOptionMap[$questionCurrent->getId()] ?? []
            ];
        }

        if (count($questionsResponse) > 0) 
        {
            $lastQuestionCurrent=$questionsResponse[count($questionsResponse) - 1];
            $nextCursor=(int)$lastQuestionCurrent['questionId'];
        }

        Logger::logInfo(
            'questionShowService',
            'Question show completed successfully!!',
            [
                'cursor'=>$cursor,
                'limit'=>$limit,
                'returnedQuestionCount'=>count($questionsResponse),
                'hasMore'=>$hasMore
            ]
        );

        return [
            'questions'=>$questionsResponse,
            'nextCursor'=>$hasMore?$nextCursor:null,
            'hasMore'=>$hasMore
        ];
    }
}
