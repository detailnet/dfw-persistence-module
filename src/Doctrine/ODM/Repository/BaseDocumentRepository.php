<?php

namespace Detail\Persistence\Doctrine\ODM\Repository;

use MongoRegex;

//use Doctrine\ORM\EntityManager;
//use Doctrine\ORM\EntityRepository;
//use Doctrine\ORM\Mapping\ClassMetadata as EntityMetadata;
//use Doctrine\ORM\Mapping\MappingException as DoctrineMappingException;
//use Doctrine\ORM\Mapping\ClassMetadataInfo as DoctrineAssociationType;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

use Zend\Paginator\Adapter\Callback as CallbackPaginatorAdapter;

use Detail\Commanding\Command\Listing\Filter;

use Detail\Persistence\Collection\CollectionInterface;
use Detail\Persistence\Repository;
use Detail\Persistence\Exception;

abstract class BaseDocumentRepository extends Repository\BaseRepository implements
    Repository\DocumentRepositoryInterface
{
    /**
     * @var DocumentRepository
     */
    protected $repository;

    /**
     * @var array
     */
    private $fields;

    /**
     * @param DocumentRepository $repository
     * @param array $inputFilters
     */
    public function __construct(DocumentRepository $repository, array $inputFilters = [])
    {
        parent::__construct($inputFilters);

        $this->repository = $repository;
    }

    /**
     * @param mixed $id
     * @return object
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param null $limit
     * @param null $offset
     * @return CollectionInterface
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $paginatorAdapter = new CallbackPaginatorAdapter(
            function () use ($criteria, $orderBy, $limit, $offset) {
                /** @var Iterator $results */
                $results = $this->createSelectQuery($criteria, $orderBy, $limit, $offset)->execute();

                // Return as array of documents (and not the iterator)
                return $results->toArray();
            },
            function () use ($criteria) {
                return $this->size($criteria);
            }
        );

        $collectionClass = $this->getCollectionClass();

        if (!class_exists($collectionClass)) {
            throw new Exception\RuntimeException(
                sprintf('Collection class "%s" does not exist', $collectionClass)
            );
        }

        /** @todo Check if collection class implements pagination (Zend\Paginator\Paginator) */

        return new $collectionClass($paginatorAdapter);
    }

    /**
     * @param array $criteria
     * @return int
     */
    public function size(array $criteria = [])
    {
        return $this->createSelectQuery($criteria)->execute()->count();
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param null $limit
     * @param null $offset
     * @return Query
     * @todo Support filtering for ReferenceOne (operation: references()) and ReferenceMany (operation: includesReferenceTo())
     */
    protected function createSelectQuery(
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        $queryBuilder = $this->createQueryBuilder();

        if ($criteria !== null && count($criteria) > 0) {
            $this->applyCriteriaToQueryBuilder($criteria, $queryBuilder);
        }

        if ($orderBy !== null) {
            foreach ($this->getOrderBy($orderBy) as $sort) {
                $queryBuilder->sort($this->getField($sort->getProperty()), $sort->getDirection());
            }
        }

        if ($limit !== null) {
            $queryBuilder->limit($limit);
        }

        if ($offset !== null) {
            $queryBuilder->skip($offset);
        }

        return $queryBuilder->getQuery();
    }

    /**
     * @return QueryBuilder
     */
    protected function createQueryBuilder()
    {
        $dm = $this->repository->getDocumentManager();
        return $dm->createQueryBuilder($this->repository->getDocumentName());
    }

    /**
     * @param array $criteria
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    protected function applyCriteriaToQueryBuilder(array $criteria, QueryBuilder $queryBuilder = null)
    {
        if ($queryBuilder === null) {
            $queryBuilder = $this->createQueryBuilder();
        }

        foreach ($criteria as $field => $value) {
            $operator = is_array($value) ? 'in' : 'equals';

            if ($value instanceof Filter) {
                $filter = $value;
                $field = $filter->getProperty();
                $operator = $this->getQueryOperator($filter);
                $value = $filter->getValue();
            }

            if ($operator === 'regex') {
                $operator = 'equals';
                $value = new MongoRegex(sprintf('/%s/i', $value));
            }

            if (!is_callable([$queryBuilder, $operator])) {
                throw new Exception\RuntimeException(
                    sprintf('Unsupported filter operator "%s"', $operator)
                );
            }

            $mappedField = $this->getField($field);

            // Permit to filter IN values when null is present
            if ($operator === 'in' && in_array(null, $value, true)) {
                $queryBuilder
                    ->field($mappedField) // Not necessary
                    ->addOr($queryBuilder->expr()->field($mappedField)->in($value)) // No need to filter out the null
                    ->addOr($queryBuilder->expr()->field($mappedField)->exists(false));

                continue;
            }

            $queryBuilder->field($mappedField)->$operator($value);
        }

        return $queryBuilder;
    }

    /**
     * @return string
     */
    protected function getDocumentAlias()
    {
        $nameParts = explode('\\', $this->repository->getDocumentName());

        return lcfirst($nameParts[count($nameParts) - 1]);
    }

    /**
     * @param Filter $filter
     * @return string
     */
    protected function getQueryOperator(Filter $filter)
    {
        $operator = $filter->getOperator();
        $operatorMap = [
            Filter::OPERATOR_SMALLER_THAN           => 'lt',
            Filter::OPERATOR_SMALLER_THAN_OR_EQUALS => 'lte',
            Filter::OPERATOR_EQUALS                 => 'equals',
            Filter::OPERATOR_GREATER_THAN_OR_EQUALS => 'gte',
            Filter::OPERATOR_GREATER_THAN           => 'gt',
            Filter::OPERATOR_NOT_EQUALS             => 'notEqual',
            Filter::OPERATOR_IN                     => 'in',
            Filter::OPERATOR_NOT_IN                 => 'notIn',
            Filter::OPERATOR_LIKE                   => 'regex',
        ];

        return isset($operatorMap[$operator]) ? $operatorMap[$operator] : $operator;
    }

    /**
     * @param object $document
     */
    protected function persistDocument($document)
    {
        $documentManager = $this->repository->getDocumentManager();
        $documentManager->persist($document);
        $documentManager->flush();
    }

    /**
     * @param object $document
     */
    protected function removeDocument($document)
    {
        $documentManager = $this->repository->getDocumentManager();
        $documentManager->remove($document);
        $documentManager->flush();
    }

    /**
     * @param string $field
     * @return string
     */
    private function getField($field)
    {
        if ($this->fields === null) {
            $dm = $this->repository->getDocumentManager();
            /** @todo Investigate if produces performance problems */
            $entityMeta = $dm->getClassMetadata($this->repository->getDocumentName());

            foreach ($entityMeta->fieldMappings as $fieldName => $fieldMapping) {
                $this->fields[$fieldMapping['name']] = $fieldName;
            }
        }

        return isset($this->fields[$field]) ? $this->fields[$field] : $field;
    }
}
