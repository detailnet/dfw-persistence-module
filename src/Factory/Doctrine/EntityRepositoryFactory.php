<?php

namespace Detail\Persistence\Factory\Doctrine;

use Interop\Container\ContainerInterface;

use Detail\Persistence\Repository\EntityRepositoryInterface;
use Detail\Persistence\Exception;

abstract class EntityRepositoryFactory extends BaseRepositoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return EntityRepositoryInterface
     */
    public function createRepository(ContainerInterface $container)
    {
        $entityName = $this->getEntityName();
        $repositoryName = $this->getRepositoryName();

        if (!class_exists($repositoryName)) {
            throw new Exception\RuntimeException(
                sprintf("Repository class %s not found", $repositoryName)
            );
        }

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
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
