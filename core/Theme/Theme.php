<?php

namespace Forum9000\Theme;

/**
 * A Theme is a collection of template overrides and resources that change how
 * the website appears.
 */
class Theme {
    /**
     * User-friendly name
     * 
     * @var string;
     */
    private $name;
    
    /**
     * Machine-friendly name. Must be unique.
     * 
     * @var string;
     */
    private $machine_name;
    
    /**
     * List of paths this theme provides.
     */
    private $paths;
    
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
    
    public function getPaths() : string {
        return $this->paths;
    }
    
    public function setPaths(string $paths) : self {
        $this->paths = $paths;
        
        return $this;
    }
}