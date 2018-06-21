<?php
/**
 * Created by PhpStorm.
 * User: murilo
 * Date: 2/28/17
 * Time: 5:06 PM
 */

namespace Application\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Log extends Model
{

    /**
     * @var array
     */
    protected $fillable = [
        'log',
    ];
}