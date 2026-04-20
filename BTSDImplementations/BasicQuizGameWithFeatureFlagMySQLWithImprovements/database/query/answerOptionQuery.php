<?php
declare(strict_types=1);


class AnswerOptionQuery
{
    public function getSqlQuery():string
    {
        return "SELECT id, text, type, question_id, is_correct FROM answer_options ORDER BY id ASC;";
    }
}




?>