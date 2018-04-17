<?php

namespace Forum9000\MarkupLanguage\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class MarkupLanguage {
    /**
     * @Required
     *
     * @var string
     */
    public $language;

    /**
     * @Required
     *
     * @var string
     */
    public $name;

    public function getLanguage() : string {
        return $this->language;
    }

    public function getName() : string {
        return $this->name;
    }
}
