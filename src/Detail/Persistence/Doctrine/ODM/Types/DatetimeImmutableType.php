<?php

namespace Detail\Persistence\Doctrine\ODM\Types;

use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;

use Doctrine\ODM\MongoDB\Types\DateType;

class DatetimeImmutableType extends DateType
{
    const NAME = 'datetime_immutable';

    /**
     * Converts a value to a DateTimeImmutable.
     *
     * Supports microseconds.
     *
     * @throws InvalidArgumentException if $value is invalid
     * @param \DateTimeInterface|\MongoDate|int|float $value
     * @return DateTimeImmutable
     */
    public static function getDateTime($value)
    {
        $dateTime = parent::getDateTime($value);

        return $dateTime instanceof DateTimeImmutable
            ? $dateTime
            : self::createImmutableDateTimeFromMutable($dateTime);
    }

    /**
     * @param DateTime|null $date
     * @return DateTimeImmutable
     */
    private static function createImmutableDateTimeFromMutable(DateTime $date)
    {
        if (method_exists(DateTimeImmutable::CLASS, 'createFromMutable')) {
            return DateTimeImmutable::createFromMutable($date);
        }

        // Fallback for PHP < 5.6
        return new DateTimeImmutable($date->format('Y-m-d H:i:s'), $date->getTimezone());
    }
}
