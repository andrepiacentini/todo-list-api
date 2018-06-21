<?php

namespace Application\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Zend\Crypt\Password\Bcrypt;

class User extends Authenticable
{

//    use EntrustUserTrait;

    use SoftDeletes;
    use Pagination;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'username',
        'password',
        'remember_token',
        'nickname',
        'social_networks',
        'is_active',
        'phone',
        'last_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $messages = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function blacklist()
    {
        return $this->hasMany(LoginBlacklist::class);
    }

    public function rememberToken($value) {
        $this->attributes['remember_token'] = $value;
    }

    public function setPasswordAttribute($value) {
        if ($value!="") {
            //TODO temporarily removed validation
            $validator = new \Application\Validators\Password();
            if ($validator->isValid($value)) {
                $bcrypt = new Bcrypt();
                $this->attributes['password'] = $bcrypt->create($value);
            }
            else {
                $this->messages[] = $validator->getMessages();
                // Gera um exception
                throw new \Exception("password not valid");
            }
        }
    }

    public function isValid() {
        if (count($this->messages)>0) return $this->messages;
        return true;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function areaPermissions() {
        return $this->hasMany(AreaPermission::class);
    }

    public function getImageAttribute($image) {
        return UtilsFacade::prependServerUrl($image);
    }
}
