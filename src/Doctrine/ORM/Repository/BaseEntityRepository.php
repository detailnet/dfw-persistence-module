<?php

namespace Detail\Persistence\Doctrine\ORM\Repository;

use Traversable;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata as EntityMetadata;
use Doctrine\ORM\Mapping\MappingException as DoctrineMappingException;
use Doctrine\ORM\Mapping\ClassMetadataInfo as DoctrineAssociationType;
use Doctrine\ORM\QueryBuilder;

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
        array $inputFilters = []
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
     * @param array|null $orderBy
     * @param integer|null $limit
     * @param integer|null $offset
     * @return CollectionInterface
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getPaginatedCollection($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param array $identifiers
     * @return array
     */
    public function findByIdentifiers(array $identifiers)
    {
        $identifierIndex = 0;
        $alias = $this->getEntityAlias();
        $queryBuilder = $this->createQuery($alias);

        foreach ($identifiers as $key => $values) {
            $queryBuilder->orWhere(sprintf('%s.%s IN (:p%s)', $alias, $key, ++$identifierIndex))
                ->setParameter('p' . $identifierIndex, $values);
        }

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * Get entities by identifier.
     *
     * Note that the order of the returned entities may not match the provided order.
     *
     * @param mixed $values Entity identifier or the entity (or a listing of those)
     * @return array
     */
    public function getByIdentifiers($values)
    {
        $entities = [];
        $meta = $this->getEntityMetadata();
        $identifiers = [];

        // Group provided values by identifier they belong to
        $addIdentifier = function ($key, $value) use (&$identifiers, $meta) {
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
                if (!$meta->hasField($key)) {
                    throw new Exception\RuntimeException(
                        sprintf(
                            'Identifier "%s" is not a field of %s',
                            $key,
                            $meta->name
                        )
                    );
                }

                $identifiers[$key] = [];
            }

            $identifiers[$key][] = $value;
        };

        if (!is_array($values) && !($values instanceof Traversable)) {
            $values = [$values];
        }

        foreach ($values as $value) {
            if (is_a($value, $meta->name)) {
                // If already instance of resulting type, pass trough without query to db
                $entities[] = $value;
                continue;
            }

            $identifier = $this->getIdentifierForValue($value);

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
     * Get entities by identifier for a given return type.
     *
     * Note that the order of the returned entities may not match the provided order.
     *
     * @param mixed $values Entity identifier or the entity (or a listing of those)
     * @param boolean $returnType "collection" or "single"
     * @return mixed
     */
    public function getByIdentifiersForType($values, $returnType = null)
    {
        if (empty($values) // No need to look up entities when none are provided
            || ($returnType == self::RETURN_TYPE_COLLECTION && !is_array($values))
            || ($returnType == self::RETURN_TYPE_SINGLE && !(is_scalar($values) || is_object($values)))
        ) {
            // Default to empty array for now (we're handling the type below)
            $entities = [];
        } else {
            $entities = $this->getByIdentifiers($values);
        }

        // Respect (or cast to) type when returning value
        switch ($returnType) {
            case self::RETURN_TYPE_COLLECTION:
                return $entities;
            case self::RETURN_TYPE_SINGLE:
                return count($entities) > 0 ? reset($entities) : null;
            default:
                // Let's guess...
                return is_array($values) ? $entities : null;
        }
    }

    /**
     * @param array $criteria
     * @return integer
     */
    public function size(array $criteria = [])
    {
        return $this->getCount($criteria);
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
        $meta = $this->getEntityMetadata();
        $typesConstraintsMapping = [
            'numeric' => ['integer', 'smallint', 'bigint'],
            'string' => ['string', 'text', 'guid'],
        ];

        if (!$meta->isIdentifierComposite) {
            $primaryIdentifier = $meta->getSingleIdentifierFieldName();
            $mapping = $meta->getFieldMapping($primaryIdentifier);

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
//        $queryBuilder = $this->getEntityRepository()->createQuery($alias);
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
     * @param array|QueryBuilder $criteria
     * @return integer
     */
    protected function getCount($criteria)
    {
        $alias = $this->getEntityAlias();
        $query = $this->getSelectQuery(sprintf('count(%s)', $alias), $criteria);

        // Make sure there's no incompatible limit and/or offset (one row is enough for the count)
        $query->setMaxResults(1);  // Limit
        $query->setFirstResult(0); // Offset

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param array|QueryBuilder $criteria
     * @param array|null $orderBy
     * @param integer|null $limit
     * @param integer|null $offset
     * @return CollectionInterface
     */
    protected function getPaginatedCollection($criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $paginatorAdapter = new CallbackPaginatorAdapter(
            function () use ($criteria, $orderBy, $limit, $offset) {
                return $this->getSelectQuery(null, $criteria, $orderBy, $limit, $offset)->getQuery()->getResult();
            },
            function () use ($criteria) {
                return $this->getCount($criteria);
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
     * @param string|null $select
     * @param array|QueryBuilder|null $criteria
     * @param array|null $orderBy
     * @param integer|null $limit
     * @param integer|null $offset
     * @return QueryBuilder
     */
    protected function getSelectQuery(
        $select = null,
        $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        if ($criteria instanceof QueryBuilder) {
            // Always work with a clone
            $query = clone $criteria;

            if ($select !== null) {
                $query->select($select);
            }
        } elseif (is_array($criteria) || is_null($criteria)) {
            $query = $this->createSelectQuery($select, $criteria, $orderBy, $limit, $offset);
        } else {
            throw new Exception\InvalidArgumentException(
                sprintf('Criteria must be an array of %s object', QueryBuilder::CLASS)
            );
        }

        return $query;
    }

    /**
     * @param string|null $select
     * @param array|null $criteria
     * @param array|null $orderBy
     * @param integer|null $limit
     * @param integer|null $offset
     * @return QueryBuilder
     */
    protected function createSelectQuery(
        $select = null,
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        $alias = $this->getEntityAlias();
        $query = $this->createQuery($alias, $select);

        if ($criteria !== null && count($criteria) > 0) {
            $this->applyCriteriaToQuery($query, $criteria);
        }

        if ($orderBy !== null) {
            foreach ($this->getOrderBy($orderBy) as $sort) {
                $direction = $sort->getDirection();

                $query->orderBy(
                    $query->expr()->$direction($this->getField($sort->getProperty(), $alias))
                );
            }
        }

        if ($limit !== null) {
            $query->setMaxResults($limit);
        }

        if ($offset !== null) {
            $query->setFirstResult($offset);
        }

        return $query;
    }

    /**
     * @param string|null $alias
     * @param string|null $select
     * @return QueryBuilder
     */
    protected function createQuery($alias = null, $select = null)
    {
        if ($alias === null) {
            $alias = $this->getEntityAlias();
        }

        $queryBuilder = $this->getEntityRepository()->createQueryBuilder($alias);

        if ($select !== null) {
            $queryBuilder->select($select);
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $query
     * @param array $criteria
     */
    protected function applyCriteriaToQuery(QueryBuilder $query, array $criteria)
    {
        $and = $query->expr()->andX();

        foreach ($criteria as $field => $value) {
            $operator = 'eq';

            if ($value instanceof Filter) {
                $filter   = $value;
                $field    = $filter->getProperty();
                $operator = $this->getQueryOperator($filter);
                $value    = $filter->getValue();
            } elseif (is_array($value)) {
                $operator = 'in';
            }

            if (!is_callable([$query->expr(), $operator])) {
                throw new Exception\RuntimeException(
                    sprintf('Unsupported filter operator "%s"', $operator)
                );
            }

            $conditions = $this->getConditionsForQueryCriteria($query, $field, $operator, $value);

            if (count($conditions) === 1) {
                $and->add($conditions[0]);
            } else {
                // Multiple (sub-)conditions for a single field are combined with OR
                $or = $query->expr()->orX();

                foreach ($conditions as $condition) {
                    $or->add($condition);
                }

                $and->add($or);
            }
        }

        $query->where($and);
    }

    /**
     * @param QueryBuilder $query
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return array
     */
    protected function getConditionsForQueryCriteria(QueryBuilder $query, $field, $operator, $value)
    {
        $alias = $this->getEntityAlias();
        $meta = $this->getEntityMetadata();

        $isManyToManyAssociation = function ($field) use ($meta) {
            try {
                $mapping = $meta->getAssociationMapping($field);

                return $mapping['type'] === DoctrineAssociationType::MANY_TO_MANY;
            } catch (DoctrineMappingException $e) {
                return false;
            }
        };

        $conditions = [];

        switch ($operator) {
            // Operators without value
            case 'isNull':
            case 'isNotNull':
                $conditions[] = $query->expr()->$operator($this->getField($field, $alias));
                break;
            // Operators with value
            default:
                $param = sprintf(':%s', $this->getField($field));

                // Only for many-to-many associations we have to use the MEMBER OF operation
                if ($isManyToManyAssociation($this->getField($field)) && in_array($operator, ['in', 'notIn'])) {
                    /** @todo Make sure identifier (and not external_id) is provided for associations (in command) */

                    if (!is_array($value)) {
                        $value = [$value];
                    }

                    foreach ($value as $i => $v) {
                        $p = $param . $i;

                        // No query builder method yet for MEMBER OF operation
                        $conditions[] = sprintf(
                            '%s %sMEMBER OF %s',
                            $p,
                            $operator == 'notIn' ? 'NOT ' : '',
                            $this->getField($field, $alias)
                        );
                        $query->setParameter($p, $v);
                    }
                } else {
                    $conditions[] = $query->expr()->$operator($this->getField($field, $alias), $param);
                    $query->setParameter($param, $value);
                }
                break;
        }

        return $conditions;
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
        $operatorMap = [
            Filter::OPERATOR_SMALLER_THAN           => 'lt',
            Filter::OPERATOR_SMALLER_THAN_OR_EQUALS => 'lte',
            Filter::OPERATOR_EQUALS                 => 'eq',
            Filter::OPERATOR_GREATER_THAN_OR_EQUALS => 'gte',
            Filter::OPERATOR_GREATER_THAN           => 'gt',
            Filter::OPERATOR_NOT_EQUALS             => 'neq',
            Filter::OPERATOR_IN                     => 'in',
            Filter::OPERATOR_NOT_IN                 => 'notIn',
            Filter::OPERATOR_LIKE                   => 'like',
        ];

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

    /**
     * @param mixed $value
     * @return string|null
     */
    protected function getIdentifier($value)
    {
        // Sub classes can determine the identifier to use based on the provided entity/value.
        $identifier = null;

        return $identifier;
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    protected function getIdentifierForValue($value)
    {
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

        return $identifier;
    }

    /**
     * @param string $field
     * @param string $alias
     * @return string
     */
    protected function getField($field, $alias = null)
    {
        $meta = $this->getEntityMetadata();

        $isAssociation = function ($field) use ($meta) {
            return in_array($field, $meta->getAssociationNames());
        };

        $convertSnakeToCamel = function ($input) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
        };

        // The field might be named after the column (snake case)..
        try {
            $field = $meta->getFieldForColumn($field);
        } catch (DoctrineMappingException $e) {
            // It might also be an association
            $camelField = $convertSnakeToCamel($field);

            if ($isAssociation($camelField)) {
                $field = $camelField;
            }
        }

        return $alias === null ? $field : sprintf('%s.%s', $alias, $field);
    }
}
