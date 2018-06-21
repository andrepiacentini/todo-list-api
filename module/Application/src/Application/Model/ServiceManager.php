<?php
namespace Application\Model;

class ServiceManager
{
    protected static $instance;

    public function __construct(\Zend\ServiceManager\ServiceManager $serviceManager)
    {
        //Just Making the service Manager Global;
        //Boot
        static::$instance = $serviceManager;
    }

    public static function get($name, $usePeeringServiceManagers = true)
    {
        return static::$instance->get($name,$usePeeringServiceManagers);
    }

    public static function getInstance()
    {
        return static::$instance;
    }

    public static function __callStatic($method,$parameters)
    {
        return static::$instance->$method(...$parameters);
    }
}