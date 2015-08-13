<?php

namespace Detail\Persistence\Factory\Doctrine;

use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Logging\LoggerChain;
use Doctrine\ORM\Cache\Logging\StatisticsCacheLogger;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Detail\Persistence\Doctrine\DBAL\Profiling\SQLProfiler;
use Detail\Persistence\Exception;
use Detail\Persistence\Options\ModuleOptions;
use Detail\Persistence\Options\Doctrine\SQLProfilerOptions;

class SQLProfilerFactory implements
    AbstractFactoryInterface
{
    /**
     * Cache of canCreateServiceWithName lookups.
     *
     * @var array
     */
    protected $lookupCache = array();

    /**
     * Determine if we can create a service with name.
     *
     * @param ServiceLocatorInterface $services
     * @param string $name
     * @param string $requestedName
     * @return boolean
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        if (array_key_exists($requestedName, $this->lookupCache)) {
            return $this->lookupCache[$requestedName];
        }

        try {
            $this->getOptions($services, $requestedName);
        } catch (Exception\ConfigException $e) {
            // There's no cache with this name set up
            $this->lookupCache[$requestedName] = false;
            return false;
        }

        $this->lookupCache[$requestedName] = true;
        return true;
    }

    /**
     * Create service with name.
     *
     * @param ServiceLocatorInterface $services
     * @param string $name
     * @param string $requestedName
     * @return SQLProfiler
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        // Note that we already checked for existence in canCreateServiceWithName()
        $options = $this->getOptions($services, $requestedName);
        $configurationName = $options->getConfiguration();

        if (!$services->has($configurationName)) {
            throw new Exception\ConfigException(
                sprintf(
                    'Doctrine configuration for "%s" does not exist for profiler "%s"',
                    $configurationName,
                    $requestedName
                )
            );
        }

        $debugStackLogger = new DebugStack();
        $cacheLogger = new StatisticsCacheLogger();

        /* @var $configuration \Doctrine\ORM\Configuration */
        $configuration = $services->get($options->getConfiguration());
        $cacheConfiguration = $configuration->getSecondLevelCacheConfiguration();

        if ($cacheConfiguration !== null) {
            $cacheConfiguration->setCacheLogger($cacheLogger);
        }

        // Keep existing logger(s)
        if ($configuration->getSQLLogger() !== null) {
            $loggerChain = $configuration->getSQLLogger();

            if (!$loggerChain instanceof LoggerChain) {
                $loggerChain = new LoggerChain();
                $loggerChain->addLogger($configuration->getSQLLogger());

                $configuration->setSQLLogger($loggerChain);
            }

            $loggerChain->addLogger($debugStackLogger);
        } else {
            $configuration->setSQLLogger($debugStackLogger);
        }

        $profiler = new SQLProfiler($debugStackLogger, $cacheLogger, $options->getName());

        return $profiler;
    }

    /**
     * @param ServiceLocatorInterface $services
     * @param $profilerName
     * @return SQLProfilerOptions
     */
    protected function getOptions(ServiceLocatorInterface $services, $profilerName)
    {
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $services->get(ModuleOptions::CLASS);
        $profilers = $moduleOptions->getDoctrine()->getSqlProfilers();

        if (!isset($profilers[$profilerName])) {
            throw new Exception\ConfigException(
                sprintf('Profiler "%s" does not exist', $profilerName)
            );
        }

        $options = $profilers[$profilerName];
//        $options->setName($profilerName);

        return $options;
    }
}
