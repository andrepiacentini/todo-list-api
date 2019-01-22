<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Todo;

use Todo\Controller\TaskController;
use Todo\Controller\TodolistController;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => []
        );
    }

    // injeta o service locator no controller
    public function getControllerConfig()
    {

        return array(
            'factories' => [
                __NAMESPACE__ .'\Controller\Todolist' => function($sm) {
                    $locator = $sm->getServiceLocator();
                    $controller = new TodolistController($locator);
                    return $controller;
                },
                __NAMESPACE__ .'\Controller\Task' => function($sm) {
                    $locator = $sm->getServiceLocator();
                    $controller = new TaskController($locator);
                    return $controller;
                },
            ],
        );
    }
}
