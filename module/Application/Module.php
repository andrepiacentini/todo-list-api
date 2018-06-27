<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Application\Controller\IndexController;
use Application\Model\Api;
use Application\Model\Security;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $sm = $e->getApplication()->getServiceManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $config = $sm->get("config");
        // when errors occurs (production only)
        if (isset($config["environment"]) && ($config["environment"]=="production")) $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onErrorOccurs'));
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
            'factories' => [
                'Application\Log' => function () {
                    $log = new Logger();
                    if (!file_exists("data")) mkdir("data");
                    if (!file_exists("data/logs")) mkdir("data/logs");
                    $writer = new Stream('data/logs/api-access_'. date("Ymd").'.log');
                    $log->addWriter($writer);
                    return $log;
                },
                'Application\Security' => function ($sm) {
                    return new Security($sm);
                },
                'Application\Api' => function ($sm) {
                    return new Api($sm);
                },
            ],
        );
    }

    // injeta o service locator no controller
    public function getControllerConfig()
    {

        return array(
            'factories' => array(
                __NAMESPACE__ .'\Controller\Index' => function($sm) {
                    $locator = $sm->getServiceLocator();
                    $controller = new IndexController($locator);
                    return $controller;
                },
            ),
        );
    }

    public function onErrorOccurs($e)
    {
        $response = $e->getResponse();
        $headers = $response->getHeaders();
        $status_code = $response->getStatusCode();
        $contentType = $headers->get('Content-Type');
        switch ($e->getError()) {
            case 'error-controller-cannot-dispatch':
                $reasonMessage = 'The requested controller was unable to dispatch the request.';
                break;
            case 'error-controller-not-found':
                $reasonMessage = 'The requested controller could not be mapped to an existing controller class.';
                break;
            case 'error-controller-invalid':
                $reasonMessage = 'The requested controller was not dispatchable.';
                break;
            case 'error-router-no-match':
                $reasonMessage = 'The requested URL could not be matched by routing.';
                break;
            default:
                $reasonMessage = 'We cannot determine at this time why a 404 was generated.';
                break;
        }


        $view = new JsonModel(array(
            'data' => [
                'error' => $reasonMessage,
                "php_raw" => $response->getContent()
            ]
        ));
        echo $view->serialize();
        http_response_code($response->getStatusCode());
        exit();


    }
}
