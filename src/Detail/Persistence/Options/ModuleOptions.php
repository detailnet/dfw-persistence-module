<?php

namespace Detail\Persistence\Options;

use Detail\Core\Options\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * @var DoctrineOptions
     */
    protected $doctrine;

    /**
     * @return DoctrineOptions
     */
    public function getDoctrine()
    {
        if ($this->doctrine === null) {
            $this->doctrine = new DoctrineOptions();
        }

        return $this->doctrine;
    }

    /**
     * @param array $doctrine
     */
    public function setDoctrine(array $doctrine)
    {
        $this->getDoctrine()->setFromArray($doctrine);
    }
}
