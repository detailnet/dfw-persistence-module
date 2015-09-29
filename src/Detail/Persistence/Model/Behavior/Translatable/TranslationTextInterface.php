<?php

namespace Detail\Persistence\Model\Behavior\Translatable;

interface TranslationTextInterface extends TranslationInterface
{
    /**
     * @return string
     */
    public function getValue();
}
