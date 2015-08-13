<?php

namespace Detail\Persistence\Listener;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;

use Detail\Log\Listener\BaseLoggingListener;

use Detail\Persistence\Doctrine\DBAL\Profiling\SQLProfiler;

class SQLProfilerLoggingListener extends BaseLoggingListener
{
    /**
     * @var SQLProfiler
     */
    protected $profiler;

    /**
     * @param SQLProfiler $profiler
     * @param LoggerInterface $logger
     */
    public function __construct(SQLProfiler $profiler, LoggerInterface $logger)
    {
        $this->profiler = $profiler;

        $this->setLogger($logger);
        $this->setLoggerPrefix('SQL Profiling');
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = -9500)
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_FINISH,
            array($this, 'onFinish'),
            $priority
        );
    }

    /**
     * @param MvcEvent $event
     */
    public function onFinish(MvcEvent $event)
    {
        $profilingStats = array(
            'query_count' => $this->profiler->getQueryCount(),
            'query_time' => $this->profiler->getQueryTime(),
        );

        $cacheStats = array(
            'hits' => $this->profiler->getCacheHitCount(),
            'misses' => $this->profiler->getCacheMissCount(),
            'puts' => $this->profiler->getCachePutCount(),
        );

        $this->log(sprintf('Profiling results for "%s"', $this->profiler->getName()), LogLevel::DEBUG, $profilingStats);
        $this->log(sprintf('Cache statistics for "%s"', $this->profiler->getName()), LogLevel::DEBUG, $cacheStats);
    }
}
