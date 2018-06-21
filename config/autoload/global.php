<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    /* Configuração do dbAdapter */
    'db' => array(
        'driver' => 'PDO',
        'dsn' => 'mysql:dbname=boilerplate_api;host=localhost;port=3306',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
            'Zend\Db\Adapter\AdapterInsert' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
        'aliases' => array(
            'ZF\OAuth2\Provider\UserId' => 'ZF\OAuth2\Provider\UserId\AuthenticationService',
        ),
    ),
    'view_manager' => array(
        'base_path' => '/'
    ),
    /* Configuração de sessão */
    'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'skt',
                'use_cookies' => true,
                'cache_expire' => 60 * 60 * 24 * 30 * 12,
                'cookie_httponly' => true,
                'cookie_lifetime' => 60 * 60 * 24 * 30 * 12,
            ),
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'validators' => array(
            'Zend\Session\Validator\RemoteAddr',
            'Zend\Session\Validator\HttpUserAgent',
        ),
        'expira-tela' => 60 * 60,
    ),
    'mensagens' => array(
        'invalido' => 'Usuário ou senha incorretos',
        'form-invalido' => 'Preencha corretamente o formulário de login',
        'expirou' => 'Sua sessão expirou. Faça login novamente.',
        'sem-acesso' => 'Sem acesso a esta página. Faça seu login.',
    ),
    'smtp' => array(
        'name'  => 'andrepiacentini.com.br',
        'host'  => 'email-ssl.com.br',
        'username' => 'noreply@engie.com.br',
        'password' => 'R8SxYGPEcAxHS27y',
        'class' => 'plain',
        'port'=> '587',
        'ssl'=> 'tls',
    ),
    'path_logico' => '',
    'version' => '0.0.1',
    'security' => array(
        'server' => 'tasklist-api'
    ),
    'environment' => 'DEVELOPMENT'
);
