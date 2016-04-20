<?php

namespace Detail\Persistence\Model;

interface TranslationTextInterface extends TranslationInterface
{
    /**
     * @return string
     */
    public function getValue();
}
