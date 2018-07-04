<?php

namespace Detail\Persistence\Doctrine;

use Doctrine\Common\Persistence\ObjectManager as ObjectManagerInterface;

trait ObjectManagersAwareTrait
{
    /**
     * @var ObjectManagerInterface[]
     */
    protected $objectManagers = array();

    /**
     * @return ObjectManagerInterface[]
     */
    public function getObjectManagers()
    {
        return $this->objectManagers;
    }

    /**
     * @param ObjectManagerInterface[] $objectManagers
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
