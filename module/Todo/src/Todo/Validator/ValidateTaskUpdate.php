<?php

namespace Todo\Validator;


use Exception;
use Todo\Dictionary\TaskStatus;

class ValidateTaskUpdate
{
    public static function validate($post_data,$todolist=null,$task=null,$user_logged): bool
    {
        if (!$task) {
            throw new Exception('task not found',401);
        }
        if ($task->todolist->user_id!=$user_logged->id) {
            throw new Exception('logged user is not a valid task\'s owner',401);
        }
        // valida os dados de post json
        if (empty($post_data["title"])) {
            throw new Exception('missing task title',401);
        }
        if (empty($post_data["description"])) {
            throw new Exception('missing task description',401);
        }
        if (empty($post_data["todolist_id"])) {
            throw new Exception('missing todolist id',401);
        }
        if (!$todolist) {
            throw new Exception('todolist not found',401);
        }
        if ($todolist->user_id!=$user_logged->id) {
            throw new Exception('logged user is not a valid todolist\'s owner',401);
        }
        if (empty($post_data["status"])) {
            throw new Exception('missing status id',401);
        }
        if ((!is_numeric($post_data["status"])) || (!isset(TaskStatus::$label[$post_data["status"]]))) {
            throw new Exception('invalid status id',401);
        }
        if (is_null($post_data["done"])) {
            throw new Exception('missing done value',401);
        }
        if (!is_bool($post_data["done"])) {
            throw new Exception('done value must be boolean',401);
        }
        if (empty($post_data["priority"])) {
            throw new Exception('missing priority value',401);
        }
        if (!in_array($post_data["priority"],range(1,10))) {
            throw new Exception('priority must be a value between 1 and 10',401);
        }
        return true;
    }

}