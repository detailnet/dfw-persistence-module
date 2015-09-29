<?php

namespace Detail\Persistence\Model\Behavior\Translatable;

use Doctrine\Common\Collections\ArrayCollection;

use Detail\Persistence\Model\TranslationInterface;

trait TranslatableTrait
{
    /**
     * @return ArrayCollection
     */
    abstract public function getTranslations();

    /**
     * @param string $locale
     * @return TranslationInterface|null
     */
    public function getTranslation($locale)
    {
        return $this->getTranslations()->get((string) $locale);
    }

    /**
     * @param string $locale
     * @return boolean
     */
    public function hasTranslation($locale)
    {
        return $this->getTranslation((string) $locale) !== null;
    }

    /**
     * Dynamic getter/setter for translation properties.
     *
     * Examples:
     * - getTitle('de')
     * - setTitle('de', 'New title')
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        $triggerError = function () use ($method) {
            // Simulate PHP's original behavior for undefined methods
            trigger_error(
                sprintf('Call to undefined method %s::%s()', get_class($this), $method),
                E_USER_ERROR
            );
        };

        // We expect a locale as first argument
        // Note that we're removing this first argument from the array for later...
        $locale = array_shift($arguments);

        // Let's see if we have a translation for given locale
        if (!$this->hasTranslation($locale)) {
            $triggerError();
        }

        // Let's see if the translation has a corresponding method
        $translation = $this->getTranslation($locale);

        if (!is_callable(array($translation, $method))) {
            $triggerError();
        }

        // Now call the method with the original arguments (except for the locale of course)
        return call_user_func_array(array($translation, $method), $arguments);
    }

    /**
     * @param string $method
     * @param TranslationText[] $texts
     */
    protected function setTranslationTexts($method, array $texts)
    {
        foreach ($texts as $text) {
            if (!$this->hasTranslation($text->getLocale())) {
                // Create the translation
                $this->createAndAddTranslation($text->getLocale());
            }

            // Update the translation using the magic setter
            call_user_func_array(
                array($this, $method),
                array((string) $text->getLocale(), $text->getValue())
            );
        }
    }

    /**
     * @param string $method
     * @return TranslationText[]
     */
    protected function getTranslationTexts($method)
    {
        $texts = array();

        foreach ($this->getTranslations() as $translation) {
            $text = call_user_func(array($translation, $method));

            if ($text !== null) {
                $texts[] = $this->createTranslationText($translation->getLocale(), $text);
            }
        }

        return $texts;
    }

    /**
     * @param string $locale
     * @param string $method
     * @return TranslationText|null
     */
    protected function getTranslationText($locale, $method)
    {
        foreach ($this->getTranslationTexts($method) as $text) {
            if ($text->getLocale() === $locale) {
                return $text;
            }
        }

        return null;
    }

    /**
     * @param string $locale
     * @param string $method
     * @return string|null
     */
    protected function getTranslationTextValue($locale, $method)
    {
        $text = $this->getTranslationText($locale, $method);

        return $text ? $text->getValue() : null;
    }

    /**
     * @param string $locale
     * @param string $value
     * @return TranslationText
     */
    protected function createTranslationText($locale, $value)
    {
        return new TranslationText($locale, $value);
    }

    /**
     * @param string $locale
     * @return TranslationInterface
     */
    abstract protected function createAndAddTranslation($locale);
}
