<?php

namespace Detail\Persistence\Doctrine\ODM\Types;

use Doctrine\ODM\MongoDB\Types\DateType;

class DatetimeImmutableNoTzType extends DatetimeNoTzType
{
    /**
     * @var string
     */
    const NAME = 'datetime_immutable_no_tz';

    /**
     * @param \DateTime $value
     * @param string|null $timezoneId
     * @return \DateTimeImmutable
     */
    public static function cloneDate($value, $timezoneId = null)
    {
        if ($timezoneId !== null) {
            return new \DateTimeImmutable($value->format('Y-m-d H:i:s'), new \DateTimeZone($timezoneId));
        } else {
            return new \DateTimeImmutable($value->format('Y-m-d H:i:s'));
        }
    }

    public static function craftDateTime($seconds, $microseconds = 0)
    {
        return \DateTimeImmutable::createFromFormat('U', $seconds, new \DateTimeZone('UTC'));
    }
}
