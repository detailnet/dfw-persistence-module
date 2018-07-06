<?php

use Detail\Persistence;
use Detail\Persistence\Factory;

return [
    'service_manager' => [
        'abstract_factories' => [
            Factory\Doctrine\CachesFactory::CLASS,
        ],
        'aliases' => [],
        'invokables' => [],
        'factories' => [
            Persistence\Options\ModuleOptions::CLASS => Factory\Options\ModuleOptionsFactory::CLASS,
        ],
        'initializers' => [],
        'shared' => [],
    ],
    'detail_persistence' => [
        'doctrine' => [
            'register_uuid_type' => false,
            'register_datetime_immutable_no_tz_type' => false,
            'register_datetime_immutable_type' => false,
            'register_datetime_no_tz_type' => false,
            'caches' => [],
        ],
    ],
];
