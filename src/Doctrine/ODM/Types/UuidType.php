<?php

namespace Detail\Persistence\Doctrine\ODM\Types;

use Ramsey\Uuid\Uuid;

use Doctrine\ODM\MongoDB\Types\Type;

class UuidType extends Type
{
    /**
     * @var string
     */
    const NAME = 'uuid';

    /**
     * Converts a value from its PHP representation to its database representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @return mixed The database representation of the value.
     */
    public function convertToDatabaseValue($value)
    {
        if ($value instanceof Uuid) {
            $value = $value->toString();
        }

        return $value;
    }

    /**
     * Converts a value from its database representation to its PHP representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @return mixed The PHP representation of the value.
     */
    public function convertToPHPValue($value)
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Uuid) {
            return $value;
        }

        $uuid = Uuid::fromString($value);

        return $uuid;
    }

    /**
     * @return string
     */
    public function closureToMongo()
    {
        return 'if ($value instanceof \'' . Uuid::CLASS . '\') { $return = $value->toString(); } else { $return = $value; }';
    }

    /**
     * @return string
     */
    public function closureToPHP()
    {
        return
            'if (empty($value)) { $return = null; } ' .
            'else if (is_object($value) && get_class($value) == \'' . Uuid::CLASS . '\') { $return = $value; } ' .
            'else { $return = \\' . Uuid::CLASS . '::fromString($value); }';
    }
}
