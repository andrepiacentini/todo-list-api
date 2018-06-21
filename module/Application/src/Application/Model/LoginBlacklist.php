<?php

/*
 * Where tokens go to die =/
 */

namespace Application\Model;

use Illuminate\Database\Eloquent\Model;

class LoginBlacklist extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'active',
        'user_id',
        'ip'
    ];

    protected $table = 'login_blacklist';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function updateDate($token) {
        self::where('token',$token)->update(['updated_at'=>date('Y-m-d H:i:s')]);
    }

    public static function killAllSessions($user_id) {
        self::where('user_id',$user_id)->update(['active'=>0]);
    }

    public static function killSessionsBeforeDate($user_id,$dt_limit) {
        self::where('user_id',$user_id)->whereDate('updated_at','<', $dt_limit)->update(['active'=>0]);
    }

    public static function isJWTActive($token) {
        $session = parent::where('token',$token)->where('active',1)->first();
        if (!$session) return false;
        return true;
    }


}
