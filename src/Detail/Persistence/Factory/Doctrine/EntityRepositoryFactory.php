<?php

namespace Detail\Persistence\Factory\Doctrine;

use Zend\ServiceManager\ServiceLocatorInterface;

//use Application\Core\Domain\InputFilter;
use Detail\Persistence\Repository\EntityRepositoryInterface;
use Detail\Persistence\Exception;

abstract class EntityRepositoryFactory extends BaseRepositoryFactory
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return EntityRepositoryInterface
     */
    public function createRepository(ServiceLocatorInterface $serviceLocator)
    {
        $entityName = $this->getEntityName();
        $repositoryName = $this->getRepositoryName();

        if (!class_exists($repositoryName)) {
            throw new Exception\RuntimeException(
                sprintf("Repository class %s not found", $repositoryName)
            );
        }

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $serviceLocator->get('Doctrine\ORM\EntityManager');
        $entityRepository = $entityManager->getRepository($entityName);

        /** @var \Doctrine\ORM\Mapping\ClassMetadata $metadata */
        $entityMetadata = $entityManager->getClassMetadata($entityName); /** @todo Investigate if produces performance problems */

        /** @var EntityRepositoryInterface $repository */
        $repository = new $repositoryName(
            $entityManager,
            $entityRepository,
            $entityMetadata
        );

        return $repository;
    }

    /**
     * Get fully qualified class name of the entity the repository is bound to.
     *
     * @return string
     */
    abstract protected function getEntityName();
}
