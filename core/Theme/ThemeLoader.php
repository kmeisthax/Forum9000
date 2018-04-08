<?php

namespace Forum9000\Theme;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Service responsible for discovering and loading theme configuration
 * 
 * YAML files only!
 * 
 * TODO: How does Symfony normally handle loading config files? We should do that
 */
class ThemeLoader extends FileLoader {
    /**
     * Load a theme.yml file and produce a Theme from it.
     */
    public function load($resource, $type = null) : Theme {
        $theme_config = Yaml::parse(file_get_contents($resource));
        
        $theme = new Theme();
        $theme->setName($theme_config["name"]);
        $theme->setMachineName($theme_config["machine_name"]);
        $theme->setPaths($theme_config["paths"]);
        
        return $theme;
    }
    
    /**
     * Check if we can load some resource.
     */
    public function supports($resource, $type = null) {
        return is_string($resource) && pathinfo($resource, PATHINFO_EXTENSION) === "yaml";
    }
}