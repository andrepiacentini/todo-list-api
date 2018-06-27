<?php
namespace Application\Traits;

use Application\Model\ServiceManager;

trait Log {
    static public function registerInLog($message,$extra_info = null) {
        $sm = ServiceManager::getInstance();
        $log = $sm->get("Application\Log");
        $log->log(LOG_NOTICE,$message,[
            "ip" => $_SERVER["REMOTE_ADDR"],
            $extra_info
        ]);
    }
}