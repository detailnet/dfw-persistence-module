<?php

namespace Detail\Persistence\Options\Doctrine;

use Detail\Core\Options\AbstractOptions;

class SQLProfilerOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $name = 'orm_default';

    /**
     * @var string
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $logger;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration ?: ('doctrine.configuration.' . $this->getName());
    }

    /**
     * @param string $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}
