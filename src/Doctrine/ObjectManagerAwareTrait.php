<?php

namespace Detail\Persistence\Doctrine;

use Doctrine\Persistence\ObjectManager;

trait ObjectManagerAwareTrait
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param ObjectManager $objectManager
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return boolean
     */
    public function hasObjectManager()
    {
        return $this->objectManager !== null;
    }
}
