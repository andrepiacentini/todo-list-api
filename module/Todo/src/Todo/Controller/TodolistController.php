<?php

namespace Todo\Controller;

use Application\Controller\ApplicationController;
use Zend\Http\PhpEnvironment\Request as Request;
use Todo\Model\Todolist;

class TodolistController extends ApplicationController
{
    protected $bypass_routes = [];

    public function get(Request $request) {
        $list = Todolist::findByUserId($this->user_logged->id);
        return $this->returnData(['status' => 200, 'data' => ["Todolists" => $list]]);
    }

    public function post(Request $request) {
        // validações iniciais
        if (($return = $this->basicCheck('post')) !== NULL) return $return;
        if (!$this->isJson($request->getContent())) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing json object.']]);

        // validações dos dados passados por post
        $post_data = get_object_vars(json_decode($request->getContent()));

        if (empty($post_data["name"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing todolist name']]);
        if (empty($post_data["user_id"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing todolist user id owner']]);

        // cria
        $todolist = Todolist::create($post_data);

        return $this->returnData(['status' => 200, 'data' => ["todolist" => $todolist]]);

    }

    public function put(Request $request) {
        // validações iniciais
        if (($return = $this->basicCheck('put')) !== NULL) return $return;
        if (!$this->isJson($request->getContent())) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing json object.']]);

        // validações dos dados passados
        $post_data = get_object_vars(json_decode($request->getContent()));
        $todolist_id = $this->params()->fromRoute('id');

        if (empty($post_data["name"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing todolist name']]);
        if (empty($todolist_id)) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing todolist id']]);

        // update
        $todolist = Todolist::find($todolist_id);

        // é owner?
        if ($todolist->user_id == $this->user_logged->id) {
            $todolist->name = $post_data["name"];
            $todolist->save();
        }


        return $this->returnData(['status' => 200, 'data' => ["todolist" => $todolist]]);

    }

}
