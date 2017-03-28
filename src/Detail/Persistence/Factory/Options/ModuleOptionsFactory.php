<?php

namespace Detail\Persistence\Factory\Options;

use Interop\Container\ContainerInterface;

use Zend\ServiceManager\Factory\FactoryInterface;

use Detail\Persistence\Exception\ConfigException;
use Detail\Persistence\Options\ModuleOptions;

class ModuleOptionsFactory implements
    FactoryInterface
{
    /**
     * Create ModuleOptions
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return ModuleOptions
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');

        if (!isset($config['detail_persistence'])) {
            throw new ConfigException('Config for Detail\Persistence is not set');
        }

        return new ModuleOptions($config['detail_persistence']);
    }
}
