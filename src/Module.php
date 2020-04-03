<?php

namespace Detail\Persistence;

use Traversable;

use Doctrine\DBAL\Types as DoctrineOrmTypes;
use Doctrine\ODM\MongoDB\Types as DoctrineOdmTypes;

use Ramsey\Uuid\Doctrine\UuidType as DoctrineUuidType;
use Ramsey\Uuid\Uuid;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

use Detail\Persistence\Doctrine\ODM\Types\UuidType as DoctrineOdmUuidType;
use Detail\Persistence\Doctrine\ODM\Types\DatetimeImmutableType as DoctrineOdmDateTimeImmutType;

class Module implements
    ConfigProviderInterface
{
    /**
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        $this->bootstrapDoctrine($event);
    }

    /**
     * @return array|Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * @param MvcEvent $event
     */
    private function bootstrapDoctrine(MvcEvent $event)
    {
        $services = $event->getApplication()->getServiceManager();

        /** @var Options\ModuleOptions $moduleOptions */
        $moduleOptions = $services->get(Options\ModuleOptions::CLASS);

        if ($moduleOptions->getDoctrine()->registerUuidType()) {
            if (!class_exists(Uuid::CLASS)) {
                throw new Exception\RuntimeException(
                    sprintf(
                        'Failed to register "uuid" Doctrine mapping type: ' .
                        'Missing required class %s',
                        Uuid::CLASS
                    )
                );
            }

            if (class_exists(DoctrineOrmTypes\Type::CLASS)) {
                DoctrineOrmTypes\Type::addType(DoctrineUuidType::NAME, DoctrineUuidType::CLASS);
            }

            if (class_exists(DoctrineOdmTypes\Type::CLASS)) {
                DoctrineOdmTypes\Type::registerType(DoctrineOdmUuidType::NAME, DoctrineOdmUuidType::CLASS);
            }
        }

        if ($moduleOptions->getDoctrine()->registerDatetimeImmutableType()) {
            if (class_exists(DoctrineOdmTypes\Type::CLASS)) {
                DoctrineOdmTypes\Type::registerType(DoctrineOdmDateTimeImmutType::NAME, DoctrineOdmDateTimeImmutType::CLASS);
            }
        }

//        /** @var \Doctrine\ORM\EntityManager $entityManager */
//        $entityManager = $services->get('Doctrine\ORM\EntityManager');
//        $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('db_mytype', 'mytype');
    }
}
