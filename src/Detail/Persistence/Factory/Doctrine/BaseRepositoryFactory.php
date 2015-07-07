<?php

namespace Detail\Persistence\Factory\Doctrine;

use Zend\InputFilter\InputFilterPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

use Detail\Filtering\InputFilter;

use Detail\Persistence\Repository\RepositoryInterface;
use Detail\Persistence\Exception;

abstract class BaseRepositoryFactory implements
    FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return RepositoryInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $repository = $this->createRepository($serviceLocator);
        $repositoryFilters = $this->getRepositoryFilters();

        if ($repository instanceof InputFilter\FilterAwareInterface
            && count($repositoryFilters)
        ) {
            /** @var InputFilterPluginManager $inputFilters */
            $inputFilters = $serviceLocator->get('InputFilterManager');
            $filters = array();

            foreach ($repositoryFilters as $filterType => $filter) {
                $filters[$filterType] = $inputFilters->get($filter);
            }

            $repository->setInputFilters($filters);
        }

        return $repository;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return RepositoryInterface
     */
    abstract public function createRepository(ServiceLocatorInterface $serviceLocator);

    /**
     * @return array
     */
    protected function getRepositoryFilters()
    {
        return array();
    }

    /**
     * Get fully qualified class name of the repository.
     *
     * @return string
     */
    abstract protected function getRepositoryName();
}
