<?php
namespace Application\Model;

class UtilsFacade
{
    /**
     * Concatena a url do CDN ou do proprio servidor em uma URI
     * @param $uri
     * @return string
     */
    public static function prependServerUrl($uri) {
        if (empty($uri)) return $uri;

        $config = self::getConfig();
        $env = $config->get('environment');
        if (($env === 'PRODUCTION' || $env === 'TESTING') && $config->offsetExists('cdn_server'))  {
            return $config->get('cdn_server') . $uri;
        }

        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        return $protocol.$_SERVER['HTTP_HOST'] . $uri;
    }

    /**
     * Retorna o config do ZF
     * @return \Zend\Config\Config
     */
    public static function getConfig() {
        $configGlobal = new \Zend\Config\Config( include APPLICATION_PATH."/config/autoload/global.php");
        if (file_exists(realpath(APPLICATION_PATH.'/config/autoload/local.php'))) {
            $configLocal = new \Zend\Config\Config( include APPLICATION_PATH."/config/autoload/local.php");
            $configGlobal->merge($configLocal);
        }

        return $configGlobal;
    }

}