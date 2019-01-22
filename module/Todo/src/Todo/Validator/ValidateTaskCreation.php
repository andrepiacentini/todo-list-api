<?php
namespace Todo\Validator;

use Exception;

class ValidateTaskCreation
{
    public static function validate($post_data,$todolist,$user_logged):bool {
        if (empty($post_data["title"])) {
            throw new Exception('missing task title', 401);
        }
        if (empty($post_data["description"])) {
            throw new Exception('missing task description', 401);
        }
        if (empty($post_data["todolist_id"])) {
            throw new Exception('missing todolist id', 401);
        }
        if (!$todolist) {
            throw new Exception('todolist not found', 401);
        }
        if ($todolist->user_id!=$user_logged->id) {
            throw new Exception('logged user is not a valid todolist\'s owner', 401);
        }
        if (empty($post_data["priority"])) {
            throw new Exception('missing priority value', 401);
        }
        if (!in_array($post_data["priority"],range(1,10))) {
            throw new Exception('priority must be a value between 1 and 10', 401);
        }
        return true;
    }
}