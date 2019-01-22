<?php
return array(
    'router' => array(
        'routes' => array(
            'task' => array(
                'type' => 'Module\Router\Content',
                'priority' => 100,
                'type' => 'segment',
                'options' => array(
                    'route' => '/v1/task/[:action][/][:id][/]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Todo\Controller',
                        'controller' => 'Todo\Controller\Task',
                        'action' => 'index',
                    ),
                ),
            ),
            'todolist' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/v1/todolist[/][:action][/][:id][/]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Todo\Controller',
                        'controller' => 'Todo\Controller\Todolist',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'pt_BR',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'default_template_suffix' => 'html',
    ),
);
