<?php
declare(strict_types=1);


//so what will this file relate to now

//again insert query first which is ofc necessary

//next could be select the query usign uid

//now also update teh state is needed -- to update at each question we need to
//also mark complete if user completes no left progress hehe
//delete is needed?? maybe later ,u never know!!!


//lets start now!!!!!!!!!

namespace Database\Query;

class UserProgressStateQuery
{
    //insert query first
    public function getInsertUserProgressStateSqlQuery():string
    {
        return '
            INSERT INTO user_progress_states
            (
                uid,
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
            (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ';
    }

    //now select queries simialr to others
    public function getSelectUserProgressStateFromUidSqlQuery():string
    {
        return '
            SELECT
                id,
                uid,
                score_current,
                questions_done,
                question_id_order_json,
                question_id_order_index_current,
                question_id_current,
                is_complete,
                created_at,
                updated_at
            FROM user_progress_states
            WHERE uid=?
        ';
    }

    //make an update query now
    public function getUpdateUserProgressStateSqlQuery():string
    {
        return '
            UPDATE user_progress_states
            SET
                score_current=?,
                questions_done=?,
                question_id_order_json=?,
                question_id_order_index_current=?,
                question_id_current=?,
                is_complete=?,
                updated_at=?
            WHERE uid=?
        ';
    }

    //when to just mark complete
    public function getMarkUserProgressStateCompleteSqlQuery():string
    {
        return '
            UPDATE user_progress_states
            SET
                score_current=?,
                questions_done=?,
                question_id_order_json=?,
                question_id_order_index_current=?,
                question_id_current=?,
                is_complete=?,
                updated_at=?
            WHERE uid=?
        ';
    }

    //delete query too since if to delete it if over
    public function getDeleteUserProgressStateFromUidSqlQuery():string
    {
        return '
            DELETE FROM user_progress_states
            WHERE uid=?
        ';
    }
}
?>