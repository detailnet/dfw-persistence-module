<?php

namespace Detail\Persistence\Doctrine;

use Doctrine\Persistence\ObjectManager as ObjectManagerInterface;

trait ObjectManagerAwareTrait
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function setObjectManager(ObjectManagerInterface $objectManager)
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
