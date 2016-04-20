<?php

namespace Detail\Persistence\Doctrine\ODM\Types;

use Doctrine\ODM\MongoDB\Types\DateType;

class DatetimeNoTzType extends DateType
{
    /**
     * @var string
     */
    const NAME = 'datetime_no_tz';

    /**
     * Convert the datetime into a new datetime UTC but without transforming the value
     * (22:00 Zurich will become 22:00 UTC)
     *
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value)
    {
        if ($value === null || $value instanceof \MongoDate) {
            return $value;
        }

        $datetime = static::cloneDate(static::getDateTime($value), 'UTC');

        return new \MongoDate($datetime->format('U'), $datetime->format('u'));
    }

    public function closureToMongo()
    {
        return 'if ($value === null || $value instanceof \MongoDate) {
                    $return = $value;
                } else {
                    $datetime = \\'.get_class($this).'::cloneDate(\\'.get_class($this).'::getDateTime($value), \'UTC\');
                    $return = new \MongoDate($datetime->format(\'U\'), $datetime->format(\'u\'));
                }';
    }

    /**
     * Convert the datetime into a new datetime with the default timezone but without transforming the value
     * (22:00 UTC will become 22:00 Zurich)
     *
     * {@inheritdoc}
     */
    public function convertToPHPValue($value)
    {
        if ($value === null) {
            return null;
        }

        return static::cloneDate(static::getDateTime($value));
    }

    public function closureToPHP()
    {
        return 'if ($value === null) {
                    $return = null;
                } else {
                    $return = \\'.get_class($this).'::cloneDate(\\'.get_class($this).'::getDateTime($value));
                }';
    }

    /**
     * @param \DateTime $value
     * @param string|null $timezoneId
     * @return \DateTime
     */
    public static function cloneDate($value, $timezoneId = null)
    {
        if ($timezoneId !== null) {
            return new \DateTime($value->format('Y-m-d H:i:s'), new \DateTimeZone($timezoneId));
        } else {
            return new \DateTime($value->format('Y-m-d H:i:s'));
        }
    }

    public static function craftDateTime($seconds, $microseconds = 0)
    {
        // Override \Doctrine\ODM\MongoDB\Types\DateType::craftDateTime to return a DateTime object
        // in the 'UTC' timezone instead the current PHP one (date_default_timezone_get())
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone('UTC'));
        $datetime->setTimestamp($seconds);

        if ($microseconds > 0) {
            $datetime = \DateTime::createFromFormat(
                'Y-m-d H:i:s.u',
                $datetime->format('Y-m-d H:i:s') . '.' . $microseconds,
                new \DateTimeZone('UTC')
            );
        }

        return $datetime;
    }
}
