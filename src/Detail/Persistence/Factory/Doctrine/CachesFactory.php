<?php

namespace Detail\Persistence\Factory\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;

use DoctrineModule\Cache\ZendStorageCache;

use Zend\Cache\Storage\StorageInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Detail\Persistence\Exception;
use Detail\Persistence\Options\ModuleOptions;
use Detail\Persistence\Options\Doctrine\CacheOptions;

class CachesFactory implements
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
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        // Note that we already checked for existence in canCreateServiceWithName()
        $options = $this->getOptions($services, $requestedName);

        $storageName = $options->getStorage();

        if (!$storageName) {
            throw new Exception\ConfigException(
                sprintf('Missing configuration option "storage" for cache "%s"', $requestedName)
            );
        }

        if (!$services->has($storageName)) {
            throw new Exception\ConfigException(
                sprintf('Cache storage "%s" does not exist for cache "%s"', $storageName, $requestedName)
            );
        }

        /** @var StorageInterface $storage */
        $storage = $services->get($storageName);
        $namespace = $options->getNamespace() ?: 'DetailPersistence';

        $cache = new ZendStorageCache($storage);
        $cache->setNamespace($namespace);

        if ($options->chainToArrayCache() !== false) {
            $arrayCache = new ArrayCache();
            $arrayCache->setNamespace($namespace); // Use same namespace

            $cache = new ChainCache(array($arrayCache, $cache));
        }

        return $cache;
    }

    /**
     * @param ServiceLocatorInterface $services
     * @param $cacheName
     * @return CacheOptions
     */
    protected function getOptions(ServiceLocatorInterface $services, $cacheName)
    {
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $services->get(ModuleOptions::CLASS);
        $caches = $moduleOptions->getDoctrine()->getCaches();

        if (!isset($caches[$cacheName])) {
            throw new Exception\ConfigException(
                sprintf('Cache "%s" does not exist', $cacheName)
            );
        }

        return $caches[$cacheName];
    }
}
