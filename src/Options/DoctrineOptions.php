<?php

namespace Detail\Persistence\Options;

use Zend\Stdlib\AbstractOptions;

class DoctrineOptions extends AbstractOptions
{
    /** @var bool */
    protected $registerUuidType = false;

    /** @var bool */
    protected $registerDatetimeImmutableType = false;

    /** @var bool */
    protected $registerDatetimeNoTzType = false;

    /** @var bool */
    protected $registerDatetimeImmutableNoTzType = false;

    /** @var bool */
    protected $registerTraversableHashType = false;

    /** @var Doctrine\CacheOptions[] */
    protected $caches = [];

    public function registerUuidType(): bool
    {
        return $this->registerUuidType;
    }

    public function setRegisterUuidType(bool $regUuidType)
    {
        $this->registerUuidType = $regUuidType;
    }

    public function registerDatetimeImmutableType(): bool
    {
        return $this->registerDatetimeImmutableType;
    }

    public function setRegisterDatetimeImmutableType(bool $regDatetimeImmutableType)
    {
        $this->registerDatetimeImmutableType = $regDatetimeImmutableType;
    }

    public function registerDatetimeNoTzType(): bool
    {
        return $this->registerDatetimeNoTzType;
    }

    public function setRegisterDatetimeNoTzType(bool $regDatetimeNoTzType)
    {
        $this->registerDatetimeNoTzType = $regDatetimeNoTzType;
    }

    public function registerDatetimeImmutableNoTzType(): bool
    {
        return $this->registerDatetimeImmutableNoTzType;
    }

    public function setRegisterDatetimeImmutableNoTzType(bool $regDatetimeImmutableNoTzType)
    {
        $this->registerDatetimeImmutableNoTzType = $regDatetimeImmutableNoTzType;
    }

    public function registerTraversableHashType(): bool
    {
        return $this->registerTraversableHashType;
    }

    public function setRegisterTraversableHashType(bool $registerTraversableHashType)
    {
        $this->registerTraversableHashType = $registerTraversableHashType;
    }

    /**
     * @return Doctrine\CacheOptions[]
     */
    public function getCaches(): array
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
    protected function createOptions(array $values, $class): array
    {
        $options = [];

        foreach ($values as $name => $config) {
            $options[$name] = new $class($config);
        }

        return $options;
    }
}
