<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logging.php';

require_once __DIR__ . '/../../database/repository/gameConfigRepository.php';
require_once __DIR__ . '/../../database/repository/questionRepository.php';
require_once __DIR__ . '/../../database/repository/answerOptionRepository.php';


class QuestionSetEditService
{
    public function questionSetEditService(***):array
    {
        $questionRepository=new QuestionRepository();
        $answerOptionRepository=new AnswerOptionRepository();

        $correctAnswerCount=0;

        foreach ($answerOptions as $answerOption)
            {
                if (
                    isset($answerOption['isCorrect']) &&
                    (bool)$answerOption['isCorrect']===true
                )
                    {
                        $correctAnswerCount++;
                    }
            }

        if ($correctAnswerCount!==1)
            {
                throw new InvalidArgumentException('Exactly one correct answer option is required!!');
            }

        $questionId=$questionRepository->createQuestion($questionText,$questionType);

        if ($questionId<=0)
            {
                throw new RuntimeException('Question creation failed!!');
            }

        foreach ($answerOptions as $answerOption)
            {
                $answerOptionId=$answerOptionRepository->createAnswerOption(
                    (string)$answerOption['text'],
                    (string)$answerOption['type'],
                    $questionId,
                    (bool)$answerOption['isCorrect']
                );

                if ($answerOptionId<=0)
                    {
                        throw new RuntimeException('Answer option creation failed!!');
                    }
            }

        Logger::logInfo(
            'questionSetEditService',
            'Question add completed successfully!!',
            [
                'questionId'=>$questionId,
                'questionType'=>$questionType
            ]
        );

        return [
            'questionId'=>$questionId,
            'questionText'=>$questionText,
            'questionType'=>$questionType,
            'answerOptionCount'=>count($answerOptions),
            'isCreated'=>true
        ];
    }
}
?>