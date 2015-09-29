<?php

namespace Detail\Persistence\Model\Behavior\Translatable;

use Detail\Persistence\Model\TranslationTextInterface;

class TranslationText implements
    TranslationTextInterface
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $value;

    /**
     * @param string $locale
     * @param string $value
     */
    public function __construct($locale, $value)
    {
        $this->locale = $locale;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
