<?php

return array(
    'service_manager' => array(
        'abstract_factories' => array(
            'Detail\Persistence\Factory\Doctrine\CachesFactory',
        ),
        'aliases' => array(
        ),
        'invokables' => array(
        ),
        'factories' => array(
            'Detail\Persistence\Options\ModuleOptions' => 'Detail\Persistence\Factory\Options\ModuleOptionsFactory',
        ),
        'initializers' => array(
        ),
        'shared' => array(
        ),
    ),
    'detail_persistence' => array(
        'doctrine' => array(
            'register_uuid_type' => false,
            'caches' => array(),
        ),
    ),
);
