<?php

namespace Forum9000\Theme;

/**
 * A Theme is a collection of template overrides and other resources
 * responsible for defining the user interface of a Forum.
 */
class Theme {
    /**
     * User-friendly name
     * 
     * @var string
     */
    private $name;
    
    /**
     * Machine-friendly name. Must be unique.
     * 
     * @var string
     */
    private $machine_name;
    
    /**
     * Where the theme.yml was discovered at
     *
     * @var string
     */
    private $theme_base_path;

    /**
     * List of paths this theme provides.
     *
     * Required theme keys are "template"
     *
     * @var array
     */
    private $paths;
    
    /**
     * Parent theme's machine name (if any)
     *
     * @var string|null
     */
    private $parent_machine_name;

    public function getName() : string {
        return $this->name;
    }
    
    public function setName(string $name) : self {
        $this->name = $name;
        
        return $this;
    }
    
    public function getMachineName() : string {
        return $this->machine_name;
    }
    
    public function setMachineName(string $machine_name) : self {
        $this->machine_name = $machine_name;
        
        return $this;
    }
    
    public function getThemeBasePath() : string {
        return $this->theme_base_path;
    }

    public function setThemeBasePath(string $theme_base_path) : self {
        $this->theme_base_path = $theme_base_path;

        return $this;
    }

    public function getPaths() : array {
        return $this->paths;
    }
    
    public function setPaths(array $paths) : self {
        $this->paths = $paths;
        
        return $this;
    }

    public function getParentMachineName() : ?string {
        return $this->parent_machine_name;
    }

    public function setParentMachineName(string $parent_machine_name) : self {
        $this->parent_machine_name = $parent_machine_name;

        return $this;
    }
}