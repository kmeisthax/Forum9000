<?php

namespace Forum9000\Twig;

class InstanceOfExtension extends \Twig_Extension {
    public function getTests() {
        return array(
            new \Twig_SimpleTest('instanceof', array($this, 'isInstanceOf')),
        );
    }

    public function isInstanceOf($var, $instance) {
        $reflexionClass = new \ReflectionClass($instance);
        return $reflexionClass->isInstance($var);
    }
}