<?php

namespace Detail\Persistence\Factory\Doctrine;

use Zend\ServiceManager\ServiceLocatorInterface;

//use Application\Core\Domain\InputFilter;
use Detail\Persistence\Repository\DocumentRepositoryInterface;
use Detail\Persistence\Exception;

abstract class DocumentRepositoryFactory extends BaseRepositoryFactory
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return DocumentRepositoryInterface
     */
    public function createRepository(ServiceLocatorInterface $serviceLocator)
    {
        $documentName = $this->getDocumentName();
        $repositoryName = $this->getRepositoryName();

        if (!class_exists($repositoryName)) {
            throw new Exception\RuntimeException(
                sprintf("Repository class %s not found", $repositoryName)
            );
        }

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $documentManager */
        $documentManager = $serviceLocator->get('doctrine.documentmanager.odm_default');

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
