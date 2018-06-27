<?php

namespace Application\Model;


use Illuminate\Database\Eloquent\Model;

class AreaPermission extends Model
{
    const AREA_ADMINISTRATOR = 'administrador';
    const AREA_USER = 'usuario';
    const ALWAYS_HAVE_ACCESS = 'always-have';

    protected $table = 'area_permission';

    protected $fillable = ['user_id', 'area'];

    protected $areas_actions = [
        self::AREA_ADMINISTRATOR => [
            'Application\\Controller\\Index\\index',
        ],
        self::AREA_USER => [
            'Application\\Controller\\Index\\index',
        ],
        self::ALWAYS_HAVE_ACCESS  => [
            'Authenticate\\Controller\\Authenticate\\getTokenContent',
        ]
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getAll($user_id) {
        $areas = self::where('user_id', $user_id)->get();
        return $areas;
    }

    public function hasAccess($user_id, $wic) {
        $areas = self::where('user_id', $user_id)->get();
        $has_access = false;
        if ((is_object($areas)) && ($areas->count() > 0)) {
            foreach ($areas as $area) {
                if (in_array($wic, $this->areas_actions[$area['area']])) $has_access = true;
            }
        }
        // testa condição "always have"
        if (in_array($wic, $this->areas_actions[self::ALWAYS_HAVE_ACCESS])) $has_access = true;
        return $has_access;

    }

    /**
     * Return all areas
     * @return array
     */
    public static function getAreas() {
        $reflectedClass = new \ReflectionClass(self::class);
        $constants = $reflectedClass->getConstants();
        $areas = array();

        foreach ($constants as $constName => $constValue) {
            if (substr($constName, 0, 4) == 'AREA') {
                $areas[]['area'] = $constValue;
            }
        }

        return $areas;
    }

    /**
     * Check if area exists
     * @param $area
     * @return bool
     */
    public static function isValidArea($area) {
        $areas = self::getAreas();
        return array_search($area, array_column($areas, 'area')) !== FALSE;
    }
}
