<?php
declare(strict_types=1);


class QuestionQuery
{
    public function getSqlQuery():string
    {
        return "SELECT id, text, type FROM questions ORDER BY id ASC;";
    }
}
?>