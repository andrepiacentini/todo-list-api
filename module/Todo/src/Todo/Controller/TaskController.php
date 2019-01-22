<?php

namespace Todo\Controller;

use Application\Controller\ApplicationController;
use Carbon\Carbon;
use Todo\Dictionary\TaskStatus;
use Todo\Model\Task;
use Todo\Model\Todolist;
use Todo\Validator\ValidateTaskCreation;
use Todo\Validator\ValidateTaskUpdate;
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
            ValidateTaskCreation::validate($post_data, $todolist, $this->user_logged);

            // cria
            $post_data["done"] = false; // ao criar, nunca pode ser incluida como concluída ;-)
            $post_data["status"] = TaskStatus::STATUS_BACKLOG;
            $post_data["user_id"] = $this->user_logged->id;
            $task = Task::create($post_data);

            return $this->returnData(['status' => 200, 'data' => ["task" => $task]]);
        } catch (\Exception $e) {
            return $this->returnData(['status' => $e->getCode(), 'data' => ['message' => $e->getMessage()]]);
        }
    }

    public function put(Request $request)
    {
        try {
            // validações iniciais
            if (($return = $this->basicCheck('put')) !== null) {
                return $return;
            }
            if (!$this->isJson($request->getContent())) {
                return $this->returnData(['status' => 401, 'data' => ['message' => 'missing json object.']]);
            }

            // validações dos dados passados
            $post_data = get_object_vars(json_decode($request->getContent()));
            $task_id = $this->params()->fromRoute('id',null);
            $task = Task::find($task_id);
            $todolist = Todolist::find($post_data["todolist_id"]);

            ValidateTaskUpdate::validate($post_data, $todolist, $task, $this->user_logged);

            // update
            $task->title = $post_data["title"];
            $task->description = $post_data["description"];
            $task->todolist_id = $post_data["todolist_id"];
            $task->status = $post_data["status"];
            $task->done = $post_data["done"];
            $task->concluded_at = ($task->done) ? Carbon::now()->toDateTimeString() : null;
            $task->save();

            return $this->returnData(['status' => 200, 'data' => ["task" => $task]]);
        } catch (\Exception $e) {
            return $this->returnData(['status' => $e->getCode(), 'data' => ['message' => $e->getMessage()]]);
        }
    }


    public function delete(Request $request)
    {
        $task_id = $this->params()->fromRoute('id',null);

        $task = Task::find($task_id);
        if (!$task) {
            return $this->returnData(['status' => 401, 'data' => ['message' => 'task not found']]);
        }

        // é owner?
        if ($task->user_id != $this->user_logged->id) {
            return $this->returnData(['status' => 401, 'data' => ['message' => 'operation not allowed']]);
        }
        $return = Task::destroy($task_id);

        if (!$return) {
            return $this->returnData(['status' => 401, 'data' => ['message' => 'cannot destroy task']]);
        }

        return $this->returnData(['status' => 200, 'data' => ['message' => 'task ID ' . $task_id . ' destroyed']]);
    }

}
