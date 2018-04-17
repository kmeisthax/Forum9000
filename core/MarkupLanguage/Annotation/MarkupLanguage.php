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
    public $slug;

    /**
     * @Required
     *
     * @var string
     */
    public $name;

    public function getSlug() : string {
        return $this->slug;
    }

    public function getName() : string {
        return $this->name;
    }
}
