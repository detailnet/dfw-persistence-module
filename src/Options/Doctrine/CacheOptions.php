<?php

namespace Detail\Persistence\Options\Doctrine;

use Zend\Stdlib\AbstractOptions;

class CacheOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $storage;

    /**
     * @var string
     */
    protected $namespace = 'DetailPersistence';

    /**
     * @var boolean
     */
    protected $chainToArrayCache = true;

    /**
     * @return string
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param string $storage
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return boolean
     */
    public function chainToArrayCache()
    {
        return $this->chainToArrayCache;
    }

    /**
     * @param boolean $chainToArrayCache
     */
    public function setChainToArrayCache($chainToArrayCache)
    {
        $this->chainToArrayCache = (boolean) $chainToArrayCache;
    }
}
