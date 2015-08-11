<?php

namespace Detail\Persistence\Options;

use Detail\Core\Options\AbstractOptions;

class DoctrineOptions extends AbstractOptions
{
    /**
     * @var boolean
     */
    protected $registerUuidType = false;

    /**
     * @var Doctrine\CacheOptions[]
     */
    protected $caches = array();

    /**
     * @return boolean
     */
    public function registerUuidType()
    {
        return $this->registerUuidType;
    }

    /**
     * @param boolean $registerUuidType
     */
    public function setRegisterUuidType($registerUuidType)
    {
        $this->registerUuidType = (boolean) $registerUuidType;
    }

    /**
     * @return Doctrine\CacheOptions[]
     */
    public function getCaches()
    {
        return $this->caches;
    }

    /**
     * @param Doctrine\CacheOptions[] $caches
     */
    public function setCaches(array $caches)
    {
        $this->caches = $this->createOptions($caches, Doctrine\CacheOptions::CLASS);
    }

    /**
     * @param array $values
     * @param string $class
     * @return array
     */
    protected function createOptions(array $values, $class)
    {
        $options = array();

        foreach ($values as $name => $config) {
            $options[$name] = new $class($config);
        }

        return $options;
    }
}
