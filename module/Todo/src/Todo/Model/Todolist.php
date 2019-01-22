<?php
/**
 * Created by PhpStorm.
 * User: andrepiacentini
 * Date: 31/05/18
 * Time: 19:34
 */

namespace Todo\Model;


use Application\Model\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Todolist extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "name",
        "user_id"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function tasks() {
        return $this->hasMany(Task::class);
    }

    public function findByUserId($user_id) {
        return self::where('user_id',$user_id)
            ->with(['user','tasks'])
            ->get();
    }
}