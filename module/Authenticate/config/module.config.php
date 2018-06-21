<?php
return array(
		'router' => array(
				'routes' => array(
				    'authenticate' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/v1/authenticate[/][:action][/][:id][/]',
                            'defaults' => array(
                                '__NAMESPACE__' => 'Authenticate\Controller',
                                'controller' => 'Authenticate\Controller\Authenticate',
                                'action'     => 'index',
                            ),
                        ),
                    ),
				),
		),
        'service_manager' => array(
//        'factories' => array(
//            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
//        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'default_template_suffix' => 'html',
    ),
);
