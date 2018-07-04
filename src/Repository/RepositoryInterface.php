<?php

namespace Detail\Persistence\Repository;

use Detail\Persistence\Collection\CollectionInterface;

interface RepositoryInterface
{
    /**
     * @param array $data
     * @return object
     */
    public function create(array $data);

    /**
     * @param mixed $id
     * @return object
     */
    public function find($id);

    /**
     * @return array
     */
    public function findAll();

    /**
     * Find entities/documents by a set of criteria.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return CollectionInterface
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

//    /**
//     * Find a single entity/document by a set of criteria.
//     *
//     * @param array $criteria
//     * @param array|null $orderBy
//     * @return object|null The entity instance or NULL if the entity can not be found.
//     */
//    public function findOneBy(array $criteria, array $orderBy = null);
//
//    public function removeAll();

    /**
     * @param array $criteria
     * @return integer
     */
    public function size(array $criteria = array());
}
