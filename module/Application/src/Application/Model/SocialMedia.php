<?php
/**
 * Created by Fefo.
 * User: fefo
 * Date: 04/19/17
 * Time: 18:40 PM
 */

namespace Application\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialMedia extends Model
{
    use SoftDeletes;

    const TYPE_FACEBOOK = 'socialmedia_facebook';
    const TYPE_INSTAGRAM= 'socialmedia_instagram';
    const TYPE_TWITTER= 'socialmedia_twitter';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $table = 'social_medias';

    /**
     * @var array
     */
    protected $fillable = [
        'title',
        'url',
        'type',
        'company_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

}
