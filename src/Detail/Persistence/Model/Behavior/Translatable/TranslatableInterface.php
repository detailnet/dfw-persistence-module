<?php

namespace Detail\Persistence\Model\Behavior\Translatable;

use Doctrine\Common\Collections\ArrayCollection;

interface TranslatableInterface
{
    /**
     * @return ArrayCollection
     */
    public function getTranslations();

    /**
     * @param string $locale
     * @return TranslationInterface
     */
    public function getTranslation($locale);

    /**
     * @param string $locale
     * @return boolean
     */
    public function hasTranslation($locale);

//    /**
//     * @param string $locale
//     * @return TranslationInterface
//     */
//    public function createTranslation($locale);
}
