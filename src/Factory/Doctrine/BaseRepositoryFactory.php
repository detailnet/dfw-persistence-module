<?php

namespace Detail\Persistence\Factory\Doctrine;

use Detail\Filtering\InputFilter;
use Detail\Persistence\Repository\RepositoryInterface;
use Interop\Container\ContainerInterface;
use Zend\InputFilter\InputFilterPluginManager;
use Zend\ServiceManager\Factory\FactoryInterface;

abstract class BaseRepositoryFactory implements
    FactoryInterface
{
    /**
     * Create repository
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return RepositoryInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $repository = $this->createRepository($container);
        $repositoryFilters = $this->getRepositoryFilters();

        if ($repository instanceof InputFilter\FilterAwareInterface
            && count($repositoryFilters)
        ) {
            /** @var InputFilterPluginManager $inputFilters */
            $inputFilters = $container->get('InputFilterManager');
            $filters = [];

            foreach ($repositoryFilters as $filterType => $filter) {
                $filters[$filterType] = $inputFilters->get($filter);
            }

            $repository->setInputFilters($filters);
        }

        return $repository;
    }

    /**
     * @param ContainerInterface $container
     * @return RepositoryInterface
     */
    abstract public function createRepository(ContainerInterface $container);

    /**
     * @return array
     */
    protected function getRepositoryFilters()
    {
        return [];
    }

    /**
     * Get fully qualified class name of the repository
     *
     * @return string
     */
    abstract protected function getRepositoryName();
}
