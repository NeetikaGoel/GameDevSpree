<?php
declare(strict_types=1);

namespace Database\Query;

class QuizAttemptQuery
{
    //sql query for inserting a new quiz attempt in db with values that will be provided for placeholders
    public function getInsertSqlQuery():string
    {
        return 'INSERT INTO quiz_attempts(score_current,questions_done,question_id_order_json,question_id_order_index_current,question_id_current,is_complete,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)';
    }

    //select query for getting quiz attempt from db where we will have its id as placeholder
    public function getSelectByIdSqlQuery():string
    {
        return 'SELECT id,score_current,questions_done,question_id_order_json,question_id_order_index_current,question_id_current,is_complete,created_at,updated_at FROM quiz_attempts WHERE id=?';
    }

    //update query for updating quiz attempt in db when we will have its id and so many things as teh palceholder
    public function getUpdateSqlQuery():string
    {
        return 'UPDATE quiz_attempts SET score_current=?,questions_done=?,question_id_order_index_current=?,question_id_current=?,updated_at=? WHERE id=?';
    }

    //another query for marking quiz attempt as complete when its done where we will have so many things as placeholders
    public function getMarkCompleteSqlQuery(): string
    {
        return 'UPDATE quiz_attempts SET score_current=?,questions_done=?,question_id_order_index_current=?,question_id_current=?,is_complete=?,updated_at=? WHERE id=?';
    }

}
?>