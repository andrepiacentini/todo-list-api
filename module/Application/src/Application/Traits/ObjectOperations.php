<?php
namespace Application\Traits;

trait ObjectOperations {
    /**
     * Get model name based on the controller name
     * @return string
     */
    private function getModelName() {
        $controller = explode('\\', get_class($this))[2];
        $modelName = str_replace('Controller', '', $controller);

        return 'Application\\Model\\'.$modelName;
    }

}