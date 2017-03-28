<?php

namespace Detail\Persistence\Factory\Doctrine;

use Interop\Container\ContainerInterface;

use Detail\Persistence\Repository\DocumentRepositoryInterface;
use Detail\Persistence\Exception;

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

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $documentManager */
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
