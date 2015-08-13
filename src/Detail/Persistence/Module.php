<?php

namespace Detail\Persistence;

use Doctrine\DBAL\Types as DoctrineOrmTypes;
use Doctrine\ODM\MongoDB\Types as DoctrineOdmTypes;

use Rhumsaa\Uuid\Doctrine\UuidType as DoctrineUuidType;

use Zend\Loader\AutoloaderFactory;
use Zend\Loader\StandardAutoloader;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
//use Zend\ServiceManager\Config as ServiceConfig;
//use Zend\ServiceManager\ServiceManager;

use Detail\Persistence\Doctrine\DBAL\Profiling\SQLProfiler;
use Detail\Persistence\Doctrine\ODM\Types\UuidType as DoctrineOdmUuidType;
use Detail\Persistence\Listener\SQLProfilerLoggingListener;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ControllerProviderInterface,
    ServiceProviderInterface
{
    public function onBootstrap(MvcEvent $event)
    {
        $this->bootstrapDoctrine($event);
        $this->bootstrapProfiling($event);
    }

    public function bootstrapDoctrine(MvcEvent $event)
    {
//        $serviceManager = $event->getApplication()->getServiceManager();
        $moduleOptions = $this->getModuleOptions($event);

        if ($moduleOptions->getDoctrine()->registerUuidType()) {
            if (!class_exists('Rhumsaa\Uuid\Uuid')) {
                throw new Exception\RuntimeException(
                    'Failed to register "uuid" Doctrine mapping type: ' .
                    'Missing required class Rhumsaa\Uuid\Uuid'
                );
            }

            if (class_exists(DoctrineOrmTypes\Type::CLASS)) {
                DoctrineOrmTypes\Type::addType(DoctrineUuidType::NAME, DoctrineUuidType::CLASS);
            }

            if (class_exists(DoctrineOdmTypes\Type::CLASS)) {
                DoctrineOdmTypes\Type::registerType(DoctrineOdmUuidType::NAME, DoctrineOdmUuidType::CLASS);
            }
        }

//        /** @var \Zend\ServiceManager\ServiceManager $serviceManager */
//        $serviceManager = $event->getApplication()->getServiceManager();
//
//        /** @var \Doctrine\ORM\EntityManager $entityManager */
//        $entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
//        $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('db_mytype', 'mytype');
    }

    public function bootstrapProfiling(MvcEvent $event)
    {
        $services = $event->getApplication()->getServiceManager();
        $profilers = $this->getModuleOptions($event)->getDoctrine()->getSqlProfilers();

        if (count($profilers) === 0) {
            return;
        }

        foreach ($profilers as $profilerName => $profilerOptions) {
            $loggerName = $profilerOptions->getLogger();

            if ($loggerName !== null) {
                if (!$services->has($loggerName)) {
                    throw new Exception\ConfigException(
                        sprintf('Logger "%s" does not exist for profiler "%s"', $loggerName, $profilerName)
                    );
                }

                /** @var SQLProfiler $profiler */
                $profiler = $services->get($profilerName);
                /** @var \Psr\Log\LoggerInterface $logger */
                $logger = $services->get($loggerName);

                $listener = new SQLProfilerLoggingListener($profiler, $logger);

                $event->getApplication()->getEventManager()->attachAggregate($listener);
            }
        }
//
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

    /**
     * @param MvcEvent $event
     * @return Options\ModuleOptions
     */
    protected function getModuleOptions(MvcEvent $event)
    {
        $services = $event->getApplication()->getServiceManager();

        /** @var Options\ModuleOptions $moduleOptions */
        $moduleOptions = $services->get(__NAMESPACE__ . '\Options\ModuleOptions');

        return $moduleOptions;
    }
}
