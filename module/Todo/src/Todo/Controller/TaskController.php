<?php

namespace Todo\Controller;

use Application\Controller\ApplicationController;
use Carbon\Carbon;
use Todo\Dictionary\TaskStatus;
use Todo\Model\Task;
use Todo\Model\Todolist;
use Todo\Validator\ValidateTaskCreation;
use Zend\Http\PhpEnvironment\Request as Request;

class TaskController extends ApplicationController
{
    protected $bypass_routes = [];

    public function get(Request $request)
    {
        $task_id = $this->params()->fromRoute('id', null);

        $collection = empty($task_id) ? Task::findByUserId($this->user_logged->id) : Task::findById($task_id);
        return $this->returnData(['status' => 200, 'data' => ["task" => $collection]]);
    }

    public function post(Request $request)
    {
        try {
            // validate request
            if (($return = $this->basicCheck('post')) !== null) {
                return $return;
            }
            if (!$this->isJson($request->getContent())) {
                return $this->returnData(['status' => 401, 'data' => ['message' => 'missing json object.']]);
            }
            $post_data = get_object_vars(json_decode($request->getContent()));

            // validações dos dados passados por post
            $todolist = Todolist::find($post_data["todolist_id"]);
            ValidateTaskCreation::validate($post_data,$todolist,$this->user_logged);

            // cria
            $post_data["done"] = false; // ao criar, nunca pode ser incluida como concluída ;-)
            $post_data["status"] = TaskStatus::STATUS_BACKLOG;
            $post_data["user_id"] = $this->user_logged->id;
            $task = Task::create($post_data);

            return $this->returnData(['status' => 200, 'data' => ["task" => $task]]);
        }
        catch (\Exception $e) {
            return $this->returnData(['status' => $e->getCode(), 'data' => ['message' => $e->getMessage()]]);
        }
    }

    public function put(Request $request)
    {
        // validações iniciais
        if (($return = $this->basicCheck('put')) !== NULL) return $return;
        if (!$this->isJson($request->getContent())) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing json object.']]);

        // validações dos dados passados
        $post_data = get_object_vars(json_decode($request->getContent()));
        $task_id = $this->params()->fromRoute('id');
        // valida dados da task que sofrerá o update
        if (empty($task_id)) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing task id']]);
        $task = Task::find($task_id);
        if (!$task) return $this->returnData(['status' => 401, 'data' => ['message' => 'task not found']]);
        if ($task->todolist->user_id!=$this->user_logged->id) return $this->returnData(['status' => 401, 'data' => ['message' => 'logged user is not a valid task\'s owner']]);
        // valida os dados de post json
        if (empty($post_data["title"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing task title']]);
        if (empty($post_data["description"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing task description']]);
        if (empty($post_data["todolist_id"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing todolist id']]);
        $todolist = Todolist::find($post_data["todolist_id"]);
        if (!$todolist) return $this->returnData(['status' => 401, 'data' => ['message' => 'todolist not found']]);
        if ($todolist->user_id!=$this->user_logged->id) return $this->returnData(['status' => 401, 'data' => ['message' => 'logged user is not a valid todolist\'s owner']]);
        if (empty($post_data["status"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing status id']]);
        if ((!is_numeric($post_data["status"])) || (!isset(TaskStatus::$label[$post_data["status"]]))) return $this->returnData(['status' => 401, 'data' => ['message' => 'invalid status id']]);
        if (is_null($post_data["done"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing done value']]);
        if (!is_bool($post_data["done"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'done value must be boolean']]);
        if (empty($post_data["priority"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing priority value']]);
        if (!in_array($post_data["priority"],range(1,10))) return $this->returnData(['status' => 401, 'data' => ['message' => 'priority must be a value between 1 and 10']]);

        // update
        $task->title = $post_data["title"];
        $task->description = $post_data["description"];
        $task->todolist_id = $post_data["todolist_id"];
        $task->status = $post_data["status"];
        $task->done = $post_data["done"];
        $task->concluded_at = ($task->done) ? Carbon::now()->toDateTimeString() : null;
        $task->save();

        return $this->returnData(['status' => 200, 'data' => ["task" => $task]]);

    }


    public function delete(Request $request)
    {
        $task_id = $this->params()->fromRoute('id');

        $task = Task::find($task_id);
        if (!$task) return $this->returnData(['status' => 401, 'data' => ['message' => 'task not found']]);

        // é owner?
        if ($task->user_id != $this->user_logged->id) return $this->returnData(['status' => 401, 'data' => ['message' => 'operation not allowed']]);
        $return = Task::destroy($task_id);

        if (!$return) return $this->returnData(['status' => 401, 'data' => ['message' => 'cannot destroy task']]);

        return $this->returnData(['status' => 200, 'data' => ['message' => 'task ID ' . $task_id . ' destroyed']]);
    }

}
