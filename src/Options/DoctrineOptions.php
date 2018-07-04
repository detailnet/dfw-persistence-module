<?php

namespace Detail\Persistence\Options;

use Zend\Stdlib\AbstractOptions;

class DoctrineOptions extends AbstractOptions
{
    /**
     * @var boolean
     */
    protected $regUuidType = false;

    /**
     * @var boolean
     */
    protected $regDatetimeImmutableType = false;

    /**
     * @var boolean
     */
    protected $regDatetimeNoTzType = false;

    /**
     * @var boolean
     */
    protected $regDatetimeImmutableNoTzType = false;

    /**
     * @var Doctrine\CacheOptions[]
     */
    protected $caches = [];

    /**
     * @return boolean
     */
    public function registerUuidType()
    {
        return $this->regUuidType;
    }

    /**
     * @param boolean $regUuidType
     */
    public function setRegisterUuidType($regUuidType)
    {
        $this->regUuidType = (boolean) $regUuidType;
    }

    /**
     * @return boolean
     */
    public function registerDatetimeImmutableType()
    {
        return $this->regDatetimeImmutableType;
    }

    /**
     * @param boolean $regDatetimeImmutableType
     */
    public function setRegisterDatetimeImmutableType($regDatetimeImmutableType)
    {
        $this->regDatetimeImmutableType = (boolean) $regDatetimeImmutableType;
    }

    /**
     * @return boolean
     */
    public function registerDatetimeNoTzType()
    {
        return $this->regDatetimeNoTzType;
    }

    /**
     * @param boolean $regDatetimeNoTzType
     */
    public function setRegisterDatetimeNoTzType($regDatetimeNoTzType)
    {
        $this->regDatetimeNoTzType = (boolean) $regDatetimeNoTzType;
    }

    /**
     * @return boolean
     */
    public function registerDatetimeImmutableNoTzType()
    {
        return $this->regDatetimeImmutableNoTzType;
    }

    /**
     * @param boolean $regDatetimeImmutableNoTzType
     */
    public function setRegisterDatetimeImmutableNoTzType($regDatetimeImmutableNoTzType)
    {
        $this->regDatetimeImmutableNoTzType = (boolean) $regDatetimeImmutableNoTzType;
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
