<?php

namespace Todo\Controller;

use Application\Controller\ApplicationController;
use Carbon\Carbon;
use Todo\Dictionary\TaskStatus;
use Todo\Model\Task;
use Todo\Model\Todolist;
use Zend\Http\PhpEnvironment\Request as Request;

class TaskController extends ApplicationController
{
    protected $bypass_routes = [];

    public function get(Request $request) {
        $task_id = $this->params()->fromRoute('id');
        if ((!is_numeric($task_id)) || ($task_id<=0)) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing task id.']]);
        $task = Task::find($task_id);
        $task->status_description = TaskStatus::$label[$task->status];
        if (!$task) return $this->returnData(['status' => 401, 'data' => ['message' => 'task not found.']]);
        if ($task->todolist->user_id!=$this->user_logged->id) return $this->returnData(['status' => 401, 'data' => ['message' => 'permission denied.']]);
        return $this->returnData(['status' => 200, 'data' => ["task" => $task]]);
    }

    public function post(Request $request) {
        // validações iniciais
        if (($return = $this->basicCheck('post')) !== NULL) return $return;
        if (!$this->isJson($request->getContent())) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing json object.']]);

        // validações dos dados passados por post
        $post_data = get_object_vars(json_decode($request->getContent()));

        if (empty($post_data["title"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing task title']]);
        if (empty($post_data["description"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing task description']]);
        if (empty($post_data["todolist_id"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing todolist id']]);
        $todolist = Todolist::find($post_data["todolist_id"]);
        if (!$todolist) return $this->returnData(['status' => 401, 'data' => ['message' => 'todolist not found']]);
        if ($todolist->user_id!=$this->user_logged->id) return $this->returnData(['status' => 401, 'data' => ['message' => 'logged user is not a valid todolist\'s owner']]);

        // cria
        $post_data["done"] = false; // ao criar, nunca pode ser incluida como concluída ;-)
        $post_data["status"] = TaskStatus::STATUS_BACKLOG;
        $task = Task::create($post_data);

        return $this->returnData(['status' => 200, 'data' => ["task" => $task]]);

    }

    public function put(Request $request) {
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

    public function delete(Request $request) {
        $task_id = $this->params()->fromRoute('id');
        // valida dados da task que sofrerá o update
        if (empty($task_id)) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing task id']]);
        $task = Task::find($task_id);
        if (!$task) return $this->returnData(['status' => 401, 'data' => ['message' => 'task not found']]);
        if ($task->todolist->user_id!=$this->user_logged->id) return $this->returnData(['status' => 401, 'data' => ['message' => 'logged user is not a valid task\'s owner']]);

        // apaga
        $task->delete();
        return $this->returnData(['status' => 200, 'data' => ["messagem" => "task " . $task->id . " deleted"]]);
    }

}
