<?php

namespace Todo\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Todo\Dictionary\TaskStatus;

class Task extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        "title",
        "user_id",
        "description",
        "todolist_id",
        "status",
        "done",
        "priority"
    ];

    protected $cast = [
        "status_description"
    ];

    public function todolist()
    {
        return $this->belongsTo(Todolist::class);
    }

    public function setStatusDescriptionAttribute()
    {
        return TaskStatus::$label[$this->status];
    }

    public function findByUserId($user_id)
    {
        return self::where('user_id',$user_id)
            ->with(['todolist'])
            ->get();
    }

    public function findByTodolistId($todolist_id)
    {
        $task = self::where('todolist_id',$todolist_id)
            ->with(['todolist'])
            ->get();
        $task->status_description = TaskStatus::$label[$task->status];
        return $task;
    }

    public function findById($task_id)
    {
        $task = self::where('id',$task_id)
            ->with(['todolist'])
            ->first();
        if ($task) {
            $task->status_description = TaskStatus::$label[$task->status];
        }
        return $task;
    }
}