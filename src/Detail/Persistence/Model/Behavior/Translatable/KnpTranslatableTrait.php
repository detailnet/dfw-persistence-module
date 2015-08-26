<?php

namespace Detail\Persistence\Model\Behavior\Translatable;

use Knp\DoctrineBehaviors\Model as ORMBehaviors;

trait KnpTranslatableTrait
{
    /**
     * @param string $locale
     * @return TranslationInterface
     */
    protected function createAndAddTranslation($locale)
    {
        $class = self::getTranslationEntityClass();
        /** @var ORMBehaviors\Translatable\Translation $translation */
        $translation = new $class();
        $translation->setLocale($locale);

        $this->addTranslation($translation);

        return $translation;
    }

    /**
     * @param ORMBehaviors\Translatable\Translation $translation
     * @return $this
     */
    abstract public function addTranslation($translation);

    /**
     * @return string
     */
    abstract public function getTranslationEntityClass();
}
