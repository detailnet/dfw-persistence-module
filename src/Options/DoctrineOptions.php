<?php

namespace Detail\Persistence\Options;

use Zend\Stdlib\AbstractOptions;

class DoctrineOptions extends AbstractOptions
{
    /**
     * @var boolean
     */
    protected $registerUuidType = false;

    /**
     * @var boolean
     */
    protected $registerDatetimeImmutableType = false;

    /**
     * @var boolean
     */
    protected $registerDatetimeNoTzType = false;

    /**
     * @var boolean
     */
    protected $registerDatetimeImmutableNoTzType = false;

    /**
     * @var Doctrine\CacheOptions[]
     */
    protected $caches = [];

    /**
     * @return boolean
     */
    public function registerUuidType()
    {
        return $this->registerUuidType;
    }

    /**
     * @param boolean $regUuidType
     */
    public function setRegisterUuidType($regUuidType)
    {
        $this->registerUuidType = (boolean) $regUuidType;
    }

    /**
     * @return boolean
     */
    public function registerDatetimeImmutableType()
    {
        return $this->registerDatetimeImmutableType;
    }

    /**
     * @param boolean $regDatetimeImmutableType
     */
    public function setRegisterDatetimeImmutableType($regDatetimeImmutableType)
    {
        $this->registerDatetimeImmutableType = (boolean) $regDatetimeImmutableType;
    }

    /**
     * @return boolean
     */
    public function registerDatetimeNoTzType()
    {
        return $this->registerDatetimeNoTzType;
    }

    /**
     * @param boolean $regDatetimeNoTzType
     */
    public function setRegisterDatetimeNoTzType($regDatetimeNoTzType)
    {
        $this->registerDatetimeNoTzType = (boolean) $regDatetimeNoTzType;
    }

    /**
     * @return boolean
     */
    public function registerDatetimeImmutableNoTzType()
    {
        return $this->registerDatetimeImmutableNoTzType;
    }

    /**
     * @param boolean $regDatetimeImmutableNoTzType
     */
    public function setRegisterDatetimeImmutableNoTzType($regDatetimeImmutableNoTzType)
    {
        $this->registerDatetimeImmutableNoTzType = (boolean) $regDatetimeImmutableNoTzType;
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
        $options = [];

        foreach ($values as $name => $config) {
            $options[$name] = new $class($config);
        }

        return $options;
    }
}
