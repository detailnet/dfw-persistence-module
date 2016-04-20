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
        // Override \Doctrine\ODM\MongoDB\Types\DateType::craftDateTime to return a DateTimeImmutable object
        // in the 'UTC' timezone instead the current PHP one (date_default_timezone_get())
        $datetime = new \DateTimeImmutable();
        $datetime->setTimezone(new \DateTimeZone('UTC'));
        $datetime->setTimestamp($seconds);

        if ($microseconds > 0) {
            $datetime = \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s.u',
                $datetime->format('Y-m-d H:i:s') . '.' . $microseconds,
                new \DateTimeZone('UTC')
            );
        }

        return $datetime;
    }
}
