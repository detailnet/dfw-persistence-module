<?php

namespace Detail\Persistence\Doctrine;

use Doctrine\Persistence\ObjectManager;

trait ObjectManagersAwareTrait
{
    /**
     * @var ObjectManager[]
     */
    protected $objectManagers = [];

    /**
     * @return ObjectManager[]
     */
    public function getObjectManagers()
    {
        return $this->objectManagers;
    }

    /**
     * @param ObjectManager[] $objectManagers
     */
    public function setObjectManagers(array $objectManagers)
    {
        $this->objectManagers = $objectManagers;
    }

    /**
     * @return boolean
     */
    public function hasObjectManagers()
    {
        return count($this->objectManagers) > 0;
    }
}
