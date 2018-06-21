<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));
// Setup autoloading
require 'init_autoloader.php';

define("APPLICATION_PATH", realpath(__DIR__."/.."));

use Illuminate\Database\Capsule\Manager;

/** @var \Interop\Container\ContainerInterface $container */
$container = (file_exists('config/autoload/local.php')) ? require 'config/autoload/local.php' : require 'config/autoload/global.php';

$capsule = new Manager();
$capsule->addConnection($container['eloquent']);
$capsule->setAsGlobal();
$capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher());
$capsule->bootEloquent();

isset($_SERVER['HTTP_HOST']) ? define('CURRENT_DOMAIN', $_SERVER['HTTP_HOST']) : define('CURRENT_DOMAIN', $_SERVER['SERVER_NAME']);

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

}

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
