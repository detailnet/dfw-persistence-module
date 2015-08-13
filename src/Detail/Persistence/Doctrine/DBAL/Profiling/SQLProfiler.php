<?php

namespace Detail\Persistence\Doctrine\DBAL\Profiling;

use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\Cache\Logging\StatisticsCacheLogger;

class SQLProfiler
{
    /**
     * @var DebugStack
     */
    protected $sqlLogger;

    /**
     * @var StatisticsCacheLogger
     */
    protected $cacheLogger;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param DebugStack $sqlLogger
     * @param StatisticsCacheLogger $cacheLogger
     * @param string $name
     */
    public function __construct(DebugStack $sqlLogger, StatisticsCacheLogger $cacheLogger, $name)
    {
        $this->sqlLogger = $sqlLogger;
        $this->cacheLogger = $cacheLogger;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return integer
     */
    public function getQueryCount()
    {
        return count($this->sqlLogger->queries);
    }

    /**
     * @return float
     */
    public function getQueryTime()
    {
        $time = 0.0;

        foreach ($this->sqlLogger->queries as $query) {
            $time += $query['executionMS'];
        }

        return $time;
    }

    /**
     * @return array
     */
    public function getQueries()
    {
        return $this->sqlLogger->queries;
    }

    /**
     * @return integer
     */
    public function getCacheHitCount()
    {
        return $this->cacheLogger->getHitCount();
    }

    /**
     * @return integer
     */
    public function getCacheMissCount()
    {
        return $this->cacheLogger->getMissCount();
    }

    /**
     * @return integer
     */
    public function getCachePutCount()
    {
        return $this->cacheLogger->getPutCount();
    }
}
