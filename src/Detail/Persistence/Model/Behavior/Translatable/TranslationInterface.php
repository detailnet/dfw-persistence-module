<?php

namespace Detail\Persistence\Model\Behavior\Translatable;

interface TranslationInterface
{
    /**
     * @return string
     */
    public function getLocale();

//    /**
//     * @param TranslatableInterface $translatable
//     * @return void
//     */
//    public function setTranslatable(TranslatableInterface $translatable);
}
