<?php

namespace Application\Core\WebModule\Repository;

use Traversable;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata as EntityMetadata;
use Doctrine\ORM\Mapping\MappingException as DoctrineMappingException;
use Doctrine\ORM\Mapping\ClassMetadataInfo as DoctrineAssociationType;

use Zend\Paginator\Adapter\Callback as CallbackPaginatorAdapter;

use Detail\Commanding\Command\Listing\Filter;

use Detail\Persistence\Collection\CollectionInterface;
use Detail\Persistence\Repository;
use Detail\Persistence\Exception;

abstract class BaseEntityRepository extends Repository\BaseRepository implements
    Repository\EntityRepositoryInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityMetadata
     */
    protected $entityMetadata;

    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * @param EntityManager    $entityManager
     * @param EntityRepository $repository
     * @param EntityMetadata   $metadata
     * @param array            $inputFilters
     */
    public function __construct(
        EntityManager $entityManager,
        EntityRepository $repository,
        EntityMetadata $metadata,
        array $inputFilters = array()
    ) {
        parent::__construct($inputFilters);

        $this->entityManager = $entityManager;
        $this->entityRepository = $repository;
        $this->entityMetadata = $metadata;
    }

    /**
     * @param mixed $id
     * @return object
     */
    public function find($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return CollectionInterface
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $paginatorAdapter = new CallbackPaginatorAdapter(
            function() use ($criteria, $orderBy, $limit, $offset) {
                return $this->createSelectQuery(null, $criteria, $orderBy, $limit, $offset)->getResult();
            },
            function() use ($criteria) {
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

        $collection = new $collectionClass($paginatorAdapter);

        return $collection;
    }

    /**
     * @param array $identifiers
     * @return array
     */
    public function findByIdentifiers(array $identifiers)
    {
        $i = 0;
        $alias = $this->getEntityAlias();
        $queryBuilder = $this->getEntityRepository()->createQueryBuilder($alias);

        foreach ($identifiers as $key => $values) {
            $queryBuilder->orWhere(sprintf('%s.%s IN (:p%s)', $alias, $key, ++$i))
                ->setParameter('p' . $i, $values);
        }

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * Get entities by identifier.
     *
     * Note that the order of the returned entities mustn't match the provided order.
     *
     * @param mixed $values Entity identifier or the entity (or a listing of those)
     * @return array
     */
    public function getByIdentifiers($values)
    {
        $entities = array();
        $metadata = $this->getEntityMetadata();
        $identifiers = array();

        // Group provided values by identifier they belong to
        $addIdentifier = function($key, $value) use (&$identifiers, $metadata) {
            /** @todo If it's no scalar, cast to string */

//            if (!is_scalar($value)) {
//                throw new Exception\RuntimeException(
//                    sprintf(
//                        'Invalid value encountered for identifier "%s"; expected scalar but got %s',
//                        $key,
//                        is_object($value) ? get_class($value) : gettype($value)
//                    )
//                );
//            }

            if (!array_key_exists($key, $identifiers)) {
                if (!$metadata->hasField($key)) {
                    throw new Exception\RuntimeException(
                        sprintf(
                            'Identifier "%s" is not a field of %s',
                            $key,
                            $metadata->name
                        )
                    );
                }

                $identifiers[$key] = array();
            }

            $identifiers[$key][] = $value;
        };

        if (!is_array($values) && !($values instanceof Traversable)) {
            $values = array($values);
        }

        foreach ($values as $value) {
            if (is_a($value, $metadata->name)) {
                // If already instance of resulting type, pass trough without query to db
                $entities[] = $value;

                continue;
            }

            $identifier = $this->getIdentifier($value);

            // When no identifier is provided, use repository's primary (single) identifier
            if ($identifier === null) {
                // Only assign identifier when value matches the identifiers configured type
                // to prevent false-positives (i.e. when providing a string and identifier is integer).
                $expectedType = is_string($value) ? 'string' : null;
                $expectedType = is_numeric($value) ? 'numeric' : $expectedType;

                if ($expectedType !== null) {
                    $identifier = $this->getSingleIdentifier($expectedType);
                }
            }

            // We don't fail when we couldn't find an identifier the value belongs to.
            // It's as if the unknown identifier was never provided in the first place...
            if ($identifier !== null) {
                $addIdentifier($identifier, $value);
            }
        }

        // If no identifiers are provided, no query
        if (count($identifiers)) {
            $entities = array_merge($entities, $this->findByIdentifiers($identifiers));
        }

        return $entities;
    }

    /**
     * @param array $criteria
     * @return int
     */
    public function size(array $criteria = array())
    {
        $alias = $this->getEntityAlias();
        $query = $this->createSelectQuery(sprintf('count(%s)', $alias), $criteria);

        return (int) $query->getSingleScalarResult();
    }

    public function beginTransaction()
    {
        $this->getEntityManager()->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->getEntityManager()->commit();
    }

    public function rollbackTransaction()
    {
        $this->getEntityManager()->rollback();
    }

    /**
     * Inquire the entity repository for the single primary key
     *
     * @param string $expectedType One of 'string' or 'numeric'
     * @return string|null
     */
    public function getSingleIdentifier($expectedType = null)
    {
        $identifier = null;
        $metadata = $this->getEntityMetadata();
        $typesConstraintsMapping = array(
            'numeric' => array('integer', 'smallint', 'bigint'),
            'string' => array('string', 'text', 'guid'),
        );

        if (!$metadata->isIdentifierComposite) {
            $primaryIdentifier = $metadata->getSingleIdentifierFieldName();
            $mapping = $metadata->getFieldMapping($primaryIdentifier);

            // Only assign identifier when $expectedType matches the mapping type
            if ((!array_key_exists($expectedType, $typesConstraintsMapping))
                || in_array($mapping['type'], $typesConstraintsMapping[$expectedType])
            ) {
                $identifier = $primaryIdentifier;
            }
        }

        return $identifier;
    }

    /**
     * Remove entity from repository.
     *
     * @param object $entity
     */
    protected function remove($entity)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($entity);
        $entityManager->flush();

//      Alternative without using the entity manager
//        $alias        = $this->getEntityAlias();
//        $queryBuilder = $this->getEntityRepository()->createQueryBuilder($alias);
//
//        $queryBuilder->delete();
//
//        /** @todo We can't rely on the model having a getId() method... we should ask the repository for the identifier getter */
//        // This is a simple workaround, see to do above...
//        if (!is_callable(array($entity, 'getId'))) {
//            throw new Exception\RuntimeException(
//                sprintf(
//                    'Entity %s does not have a getId() method; it is required for deleting entities',
//                    get_class($entity)
//                )
//            );
//        }
//
//        $queryBuilder->where($queryBuilder->expr()->eq('id', $entity->getId()));
//
//        die($queryBuilder->getQuery()->getDQL());
//        // DELETE Application\Asset\WebModule\Entity\Asset asset WHERE id = 96
    }

    /**
     * @param object $entity
     */
    protected function persist($entity)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();
    }

    /**
     * @param string $select
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return \Doctrine\ORM\Query
     */
    protected function createSelectQuery(
        $select = null,
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        $entityMeta = $this->getEntityMetadata();
        $alias      = $this->getEntityAlias();

        $isAssociation = function($field) use ($entityMeta) {
            return in_array($field, $entityMeta->getAssociationNames());
        };

        $isManyToManyAssociation = function($field) use ($entityMeta) {
            try {
                $mapping = $entityMeta->getAssociationMapping($field);

                return $mapping['type'] === DoctrineAssociationType::MANY_TO_MANY;
            } catch (DoctrineMappingException $e) {
                return false;
            }
        };

        $convertSnakeToCamel = function($input) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
        };

        $getField = function($field, $withAlias = true) use ($alias, $isAssociation, $entityMeta, $convertSnakeToCamel) {
            // The field might be named after the column (snake case)..
            try {
                $field = $entityMeta->getFieldForColumn($field);
            } catch (DoctrineMappingException $e) {
                // It might also be an association
                $camelField = $convertSnakeToCamel($field);

                if ($isAssociation($camelField)) {
                    $field = $camelField;
                }
            }

            return $withAlias === false ? $field : sprintf('%s.%s', $alias, $field);
        };

        $queryBuilder = $this->getEntityRepository()->createQueryBuilder($alias);

        if ($select !== null) {
            $queryBuilder->select($select);
        }

        if ($criteria !== null && count($criteria) > 0) {
            $and = $queryBuilder->expr()->andX();

            foreach ($criteria as $field => $value) {
                $operator = 'eq';

                if ($value instanceof Filter) {
                    $filter   = $value;
                    $field    = $filter->getProperty();
                    $operator = $this->getQueryOperator($filter);
                    $value    = $filter->getValue();
                } else if (is_array($value)) {
                    $operator = 'in';
                }

                if (!is_callable(array($queryBuilder->expr(), $operator))) {
                    throw new Exception\RuntimeException(
                        sprintf('Unsupported filter operator "%s"', $operator)
                    );
                }

                /** @todo This can surely be refactored to a simpler form... */
                $conditions = array();

                switch ($operator) {
                    // Operators without value
                    case 'isNull':
                    case 'isNotNull':
                        $conditions[] = $queryBuilder->expr()->$operator($getField($field));
                        break;
                    // Operators with value
                    default:
                        $param = sprintf(':%s', $getField($field, false));

                        // Only for many-to-many associations we have to use the MEMBER OF operation
                        if ($isManyToManyAssociation($getField($field, false)) && $operator = 'in') {
                            /** @todo Make sure identifier (and not external_id) is provided for associations (in command) */

                            if (!is_array($value)) {
                                $value = array($value);
                            }

                            foreach ($value as $i => $v) {
                                $p = $param . $i;

                                // No query builder method yet for MEMBER OF operation
                                $conditions[] = sprintf('%s MEMBER OF %s', $p, $getField($field));
                                $queryBuilder->setParameter($p, $v);
                            }
                        } else {
                            $conditions[] = $queryBuilder->expr()->$operator($getField($field), $param);
                            $queryBuilder->setParameter($param, $value);
                        }
                        break;
                }

                if (count($conditions) === 1) {
                    $and->add($conditions[0]);
                } else {
                    // Multiple (sub-)conditions for a single field are combined with OR
                    $or = $queryBuilder->expr()->orX();

                    foreach ($conditions as $condition) {
                        $or->add($condition);
                    }

                    $and->add($or);
                }
            }

            $queryBuilder->where($and);
        }

        if ($orderBy !== null) {
            foreach ($this->getOrderBy($orderBy) as $sort) {
                $direction = $sort->getDirection();

                $queryBuilder->orderBy(
                    $queryBuilder->expr()->$direction($getField($sort->getProperty()))
                );
            }
        }

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        return $queryBuilder->getQuery();
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    protected function getIdentifier($value)
    {
        // Sub classes can determine the identifier to use based on the provided entity.
        $identifier = null;

        return $identifier;
    }

    /**
     * @return string
     */
    protected function getEntityAlias()
    {
        $nameParts = explode('\\', $this->getEntityName());
        $alias = lcfirst($nameParts[count($nameParts) - 1]);

        return $alias;
    }

    /**
     * @param Filter $filter
     * @return string
     */
    protected function getQueryOperator(Filter $filter)
    {
        $operator = $filter->getOperator();
        $operatorMap = array(
            Filter::OPERATOR_SMALLER_THAN           => 'lt',
            Filter::OPERATOR_SMALLER_THAN_OR_EQUALS => 'lte',
            Filter::OPERATOR_EQUALS                 => 'eq',
            Filter::OPERATOR_GREATER_THAN_OR_EQUALS => 'gte',
            Filter::OPERATOR_GREATER_THAN           => 'gt',
            Filter::OPERATOR_NOT_EQUALS             => 'neq',
            Filter::OPERATOR_IN                     => 'in',
            Filter::OPERATOR_LIKE                   => 'like',
        );

        return isset($operatorMap[$operator]) ? $operatorMap[$operator] : $operator;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return EntityRepository
     */
    protected function getEntityRepository()
    {
        return $this->entityRepository;
    }

    /**
     * @return EntityMetadata
     */
    protected function getEntityMetadata()
    {
        return $this->entityMetadata;
    }

    /**
     * @return string
     */
    protected function getEntityName()
    {
        return $this->getEntityRepository()->getClassName();
    }
}
