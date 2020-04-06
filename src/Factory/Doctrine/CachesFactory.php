<?php

namespace Detail\Persistence\Factory\Doctrine;

use Detail\Persistence\Exception;
use Detail\Persistence\Options\Doctrine\CacheOptions;
use Detail\Persistence\Options\ModuleOptions;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use DoctrineModule\Cache\ZendStorageCache;
use Interop\Container\ContainerInterface;
use Zend\Cache\Storage\StorageInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class CachesFactory implements
    AbstractFactoryInterface
{
    /**
     * Cache of canCreateServiceWithName lookups.
     *
     * @var array
     */
    protected $lookupCache = [];

    /**
     * Can the factory create an instance for the service?
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return boolean
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (array_key_exists($requestedName, $this->lookupCache)) {
            return $this->lookupCache[$requestedName];
        }

        try {
            $this->getOptions($container, $requestedName);
        } catch (Exception\ConfigException $e) {
            // There's no cache with this name set up
            $this->lookupCache[$requestedName] = false;
            return false;
        }

        $this->lookupCache[$requestedName] = true;
        return true;
    }

    /**
     * Create cache
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return ZendStorageCache
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // Note that we already checked for existence in canCreateServiceWithName()
        $options = $this->getOptions($container, $requestedName);

        $storageName = $options->getStorage();

        if (!$storageName) {
            throw new Exception\ConfigException(
                sprintf('Missing configuration option "storage" for cache "%s"', $requestedName)
            );
        }

        if (!$container->has($storageName)) {
            throw new Exception\ConfigException(
                sprintf('Cache storage "%s" does not exist for cache "%s"', $storageName, $requestedName)
            );
        }

        /** @var StorageInterface $storage */
        $storage = $container->get($storageName);
        $namespace = $options->getNamespace() ?: 'DetailPersistence';

        $cache = new ZendStorageCache($storage);
        $cache->setNamespace($namespace);

        if ($options->chainToArrayCache() !== false) {
            $arrayCache = new ArrayCache();
            $arrayCache->setNamespace($namespace); // Use same namespace

            $cache = new ChainCache([$arrayCache, $cache]);
        }

        return $cache;
    }

    /**
     * @param ContainerInterface $container
     * @param string $cacheName
     * @return CacheOptions
     */
    protected function getOptions(ContainerInterface $container, $cacheName)
    {
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::CLASS);
        $caches = $moduleOptions->getDoctrine()->getCaches();

        if (!isset($caches[$cacheName])) {
            throw new Exception\ConfigException(
                sprintf('Cache "%s" does not exist', $cacheName)
            );
        }

        return $caches[$cacheName];
    }
}
