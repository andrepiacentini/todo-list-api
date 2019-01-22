<?php
/**
 * Created by PhpStorm.
 * User: andrepiacentini
 * Date: 31/05/18
 * Time: 19:34
 */

namespace Todo\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Todo\Dictionary\TaskStatus;

class Task extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        "title",
        "description",
        "tasklist_id",
        "status",
        "done",
        "priority"
    ];

    protected $cast = [
        "status_description"
    ];

    public function todolist() {
        return $this->belongsTo(Todolist::class);
    }

    public function setStatusDescriptionAttribute() {
        return TaskStatus::$label[$this->status];
    }

    public function findByTasklistId($tasklist_id) {
        $task = self::where('list_id',$tasklist_id)
            ->with(['todolist'])
            ->get();
        $task->status_description = TaskStatus::$label[$task->status];
        return $task;
    }
}