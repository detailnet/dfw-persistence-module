<?php

namespace Detail\Persistence\Factory\Options;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Detail\Persistence\Exception\ConfigException;
use Detail\Persistence\Options\ModuleOptions;

class ModuleOptionsFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return ModuleOptions
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        if (!isset($config['detail_persistence'])) {
            throw new ConfigException('Config for Detail\Persistence is not set');
        }

        return new ModuleOptions($config['detail_persistence']);
    }
}
