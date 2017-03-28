<?php

namespace Detail\Persistence;

use Doctrine\DBAL\Types as DoctrineOrmTypes;
use Doctrine\ODM\MongoDB\Types as DoctrineOdmTypes;

use Ramsey\Uuid\Doctrine\UuidType as DoctrineUuidType;
use Ramsey\Uuid\Uuid;

use Zend\Loader\AutoloaderFactory;
use Zend\Loader\StandardAutoloader;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
//use Zend\ServiceManager\Config as ServiceConfig;
//use Zend\ServiceManager\ServiceManager;

use Detail\Persistence\Doctrine\ODM\Types\UuidType as DoctrineOdmUuidType;
use Detail\Persistence\Doctrine\ODM\Types\DatetimeImmutableNoTzType as DoctrineOdmDateTimeImmutNoTzType;
use Detail\Persistence\Doctrine\ODM\Types\DatetimeImmutableType as DoctrineOdmDateTimeImmutType;
use Detail\Persistence\Doctrine\ODM\Types\DatetimeNoTzType as DoctrineOdmDateTimeNoTzType;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ControllerProviderInterface,
    ServiceProviderInterface
{
    /**
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        $this->bootstrapDoctrine($event);
    }

    /**
     * @param MvcEvent $event
     */
    public function bootstrapDoctrine(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();

        /** @var Options\ModuleOptions $moduleOptions */
        $moduleOptions = $serviceManager->get(Options\ModuleOptions::CLASS);

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

        if ($moduleOptions->getDoctrine()->registerDatetimeNoTzType()) {
            if (class_exists(DoctrineOdmTypes\Type::CLASS)) {
                DoctrineOdmTypes\Type::registerType(DoctrineOdmDateTimeNoTzType::NAME, DoctrineOdmDateTimeNoTzType::CLASS);
            }
        }

        if ($moduleOptions->getDoctrine()->registerDatetimeImmutableNoTzType()) {
            if (class_exists(DoctrineOdmTypes\Type::CLASS)) {
                DoctrineOdmTypes\Type::registerType(DoctrineOdmDateTimeImmutNoTzType::NAME, DoctrineOdmDateTimeImmutNoTzType::CLASS);
            }
        }

//        /** @var \Zend\ServiceManager\ServiceManager $serviceManager */
//        $serviceManager = $event->getApplication()->getServiceManager();
//
//        /** @var \Doctrine\ORM\EntityManager $entityManager */
//        $entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
//        $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('db_mytype', 'mytype');
    }

    /**
     * {@inheritdoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            AutoloaderFactory::STANDARD_AUTOLOADER => array(
                StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = include __DIR__ . '/../../../config/module.config.php';

        return $config;
    }

    public function getControllerConfig()
    {
        return array();
    }

    public function getServiceConfig()
    {
        return array();
    }
}
