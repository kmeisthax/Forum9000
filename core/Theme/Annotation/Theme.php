<?php

namespace Forum9000\Theme\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Theme {
    /**
     * @var string
     */
    public $routeClass="user";
    
    public function getRouteClass() : string {
        return $this->routeClass;
    }
}