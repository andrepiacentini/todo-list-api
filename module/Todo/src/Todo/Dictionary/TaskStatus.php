<?php

namespace Todo\Dictionary;


abstract class TaskStatus
{
    const STATUS_BACKLOG    = 1;
    const STATUS_INPROGRESS = 2;
    const STATUS_RESOLVED   = 3;
    const STATUS_DONE       = 4;
    const STATUS_CLOSED     = 5;

    public static $label = [
        self::STATUS_BACKLOG        => 'backlog',
        self::STATUS_INPROGRESS     => 'em andamento',
        self::STATUS_RESOLVED       => 'resolvido',
        self::STATUS_DONE           => 'feito',
        self::STATUS_CLOSED         => 'concluído',
    ];

    public static function getAll() {
        return self::$label;
    }

}