<?php

namespace Detail\Persistence\Model;

interface TranslationTextInterface
{
    /**
     * @return string
     */
    public function getLocale();

    /**
     * @return string
     */
    public function getValue();
}
