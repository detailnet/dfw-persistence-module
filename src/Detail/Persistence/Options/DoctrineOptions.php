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
}
