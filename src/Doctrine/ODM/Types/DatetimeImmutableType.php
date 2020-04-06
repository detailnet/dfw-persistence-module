<?php

namespace Detail\Persistence\Doctrine\ODM\Types;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ODM\MongoDB\Types\DateType;
use InvalidArgumentException;
use MongoDate;

class DatetimeImmutableType extends DateType
{
    const NAME = 'datetime_immutable';

    /**
     * Converts a value to a DateTimeImmutable.
     *
     * Supports microseconds.
     *
     * @throws InvalidArgumentException if $value is invalid
     * @param DateTimeInterface|MongoDate|integer|float $value
     * @return DateTimeImmutable
     */
    public static function getDateTime($value)
    {
        $dateTime = parent::getDateTime($value);

        return $dateTime instanceof DateTimeImmutable
            ? $dateTime
            : DateTimeImmutable::createFromMutable($dateTime);
    }
}
