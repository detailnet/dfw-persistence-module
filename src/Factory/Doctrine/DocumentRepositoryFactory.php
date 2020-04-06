<?php

namespace Detail\Persistence\Factory\Doctrine;

use Detail\Persistence\Exception;
use Detail\Persistence\Repository\DocumentRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Interop\Container\ContainerInterface;

abstract class DocumentRepositoryFactory extends BaseRepositoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return DocumentRepositoryInterface
     */
    public function createRepository(ContainerInterface $container)
    {
        $documentName = $this->getDocumentName();
        $repositoryName = $this->getRepositoryName();

        if (!class_exists($repositoryName)) {
            throw new Exception\RuntimeException(
                sprintf("Repository class %s not found", $repositoryName)
            );
        }

        /** @var DocumentManager $documentManager */
        $documentManager = $container->get('doctrine.documentmanager.odm_default');

        /** @var DocumentRepositoryInterface $repository */
        $repository = new $repositoryName($documentManager->getRepository($documentName));

        return $repository;
    }

    /**
     * Get fully qualified class name of the document the repository is bound to.
     *
     * @return string
     */
    abstract protected function getDocumentName();
}
