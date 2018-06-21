<?php
return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/v1[/][:action][/][:id][/]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Application\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'JSON',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'layout/main-template' => __DIR__ . '/../view/layout/main-template.phtml',
            'layout/no-menu' => __DIR__ . '/../view/layout/main-template-no-menu.phtml',
            'layout/no-session' => __DIR__ . '/../view/layout/main-template-no-session.phtml',
            'app/index' => __DIR__ . '/../view/application/index/index.phtml',
            'app/facebook' => __DIR__ . '/../view/application/index/login_fb.phtml',
            'app/sim' => __DIR__ . '/../view/application/index/sim.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
            'main/busca' => __DIR__ . '/../view/application/index/busca.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'default_template_suffix' => 'json',
    ),
);
