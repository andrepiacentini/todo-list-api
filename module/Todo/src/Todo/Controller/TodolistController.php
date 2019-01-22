<?php

namespace Todo\Controller;

use Application\Controller\ApplicationController;
use Zend\Http\PhpEnvironment\Request as Request;
use Todo\Model\Todolist;

class TodolistController extends ApplicationController
{
    protected $bypass_routes = ['/v1/todolist/'];

    public function get(Request $request)
    {
        $todolist_id = $this->params()->fromRoute('id',null);
        $list = (empty($todolist_id)) ? Todolist::findByUserId($this->user_logged->id) : Todolist::findById($todolist_id);
        return $this->returnData(['status' => 200, 'data' => ["todolists" => $list]]);
    }

    public function post(Request $request)
    {
        // validações iniciais
        if (($return = $this->basicCheck('post')) !== NULL) return $return;
        if (!$this->isJson($request->getContent())) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing json object.']]);

        // validações dos dados passados por post
        $post_data = get_object_vars(json_decode($request->getContent()));
        if (empty($post_data["name"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing todolist name']]);
        $post_data["user_id"] = $this->user_logged->id;
        if (count(Todolist::where('user_id',$this->user_logged->id)->where('name',$post_data['name'])->get())>0) return $this->returnData(['status' => 401, 'data' => ['message' => 'a todolist with this name already exists']]);

        // cria
        $todolist = Todolist::create($post_data);

        return $this->returnData(['status' => 200, 'data' => ["todolist" => $todolist]]);

    }

    public function put(Request $request)
    {
        // validações iniciais
        if (($return = $this->basicCheck('put')) !== NULL) return $return;
        if (!$this->isJson($request->getContent())) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing json object.']]);

        // validações dos dados passados
        $post_data = get_object_vars(json_decode($request->getContent()));
        $todolist_id = $this->params()->fromRoute('id');
        if (empty($post_data["name"])) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing todolist name']]);
        if (empty($todolist_id)) return $this->returnData(['status' => 401, 'data' => ['message' => 'missing todolist id']]);
        if (count(Todolist::where('user_id',$this->user_logged->id)->where('name',$post_data['name'])->where('id','!=',$todolist_id)->get())>0) return $this->returnData(['status' => 401, 'data' => ['message' => 'a todolist with this name already exists']]);

        // update
        $todolist = Todolist::find($todolist_id);

        if (!$todolist) return $this->returnData(['status' => 401, 'data' => ['message' => 'todolist not found']]);

        // é owner?
        if ($todolist->user_id == $this->user_logged->id) {
            $todolist->name = $post_data["name"];
            $todolist->save();
        }


        return $this->returnData(['status' => 200, 'data' => ["todolist" => $todolist]]);

    }

    public function delete(Request $request)
    {
        $todolist_id = $this->params()->fromRoute('id');

        $todolist = Todolist::find($todolist_id);
        if (!$todolist) return $this->returnData(['status' => 401, 'data' => ['message' => 'todolist not found']]);

        // é owner?
        if ($todolist->user_id == $this->user_logged->id) {
            $return = Todolist::destroy($todolist_id);
        }

        if (!$return) return $this->returnData(['status' => 401, 'data' => ['message' => 'cannot destroy todolist']]);

        return $this->returnData(['status' => 200, 'data' => ['message' => 'todolist ID ' . $todolist_id . ' destroyed']]);
    }

}
