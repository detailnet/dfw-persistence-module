<?php

namespace Detail\Persistence\Repository;

interface EntityRepositoryInterface extends RepositoryInterface
{
    /**
     * @param array $identifiers
     * @return array
     */
    public function findByIdentifiers(array $identifiers);

    /**
     * Get entities by identifier.
     *
     * @param mixed $values Entity identifier or the entity (or a listing of those)
     * @return array
     */
    public function getByIdentifiers($values);

    /**
     * Inquire the entity repository for the single primary key
     *
     * @param string $expectedType One of 'string' or 'numeric'
     * @return string|null
     */
    public function getSingleIdentifier($expectedType = null);

    /**
     * Begin a transaction.
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * Commit a transaction.
     *
     * @return void
     */
    public function commitTransaction();

    /**
     * Roll back a transaction.
     *
     * @return void
     */
    public function rollbackTransaction();
}
