<?php

namespace Detail\Persistence\Doctrine\ODM\Types;

use ArrayIterator;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Types\HashType;
use Traversable;

class TraversableHashType extends HashType /** @todo Inherit from Type instead of HashType? */
{
    /** @var string */
    const NAME = 'traversable_hash';

    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValue($value)
    {
        if ($value !== null && ! is_array($value) && ! $value instanceof Traversable) {
            throw MongoDBException::invalidValueForType('TraversableHash', array('\Traversable', 'array', 'null'), $value);
        }

        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        }

        // parent::convertToDatabaseValue($value);
        return $value !== null ? (object) $value : null;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value)
    {
        return $value !== null ? new ArrayIterator((array) $value) : null;
    }
}
