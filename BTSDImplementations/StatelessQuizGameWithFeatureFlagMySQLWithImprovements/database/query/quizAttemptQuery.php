<?php
declare(strict_types=1);


class QuizAttemptQuery
{
    public function getInsertSqlQuery():string
    {
        return '
            INSERT INTO quiz_attempts
            (
                score_current,
                questions_done,
                question_id_order_json,
                question_id_order_index_current,
                question_id_current,
                is_complete,
                created_at,
                updated_at
            )
            VALUES
            (?,?,?,?,?,?,?,?)
        ';
    }

    public function getSelectByIdSqlQuery():string
    {
        return '
            SELECT
                id,
                score_current,
                questions_done,
                question_id_order_json,
                question_id_order_index_current,
                question_id_current,
                is_complete,
                created_at,
                updated_at
            FROM quiz_attempts
            WHERE id=?
        ';
    }

    public function getUpdateSqlQuery():string
    {
        return '
            UPDATE quiz_attempts
            SET
                score_current=?,
                questions_done=?,
                question_id_order_index_current=?,
                question_id_current=?,
                updated_at=?
            WHERE id=?
        ';
    }

    public function getMarkCompleteSqlQuery(): string
    {
        return '
            UPDATE quiz_attempts
            SET
                score_current=?,
                questions_done=?,
                question_id_order_index_current=?,
                question_id_current=?,
                is_complete=?,
                updated_at=?
            WHERE id=?
        ';
    }


}
?>