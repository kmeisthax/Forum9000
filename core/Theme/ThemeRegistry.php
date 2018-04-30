<?php

namespace Forum9000\Theme;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\Asset\Context\ContextInterface;

/**
 * Storage for loaded theme configuration & such.
 *
 * Also, responsible for determining the override hierarchy for a given theme.
 */
class ThemeRegistry {
    /**
     * That which finds our theme yamls
     *
     * @var Forum9000\Theme\ThemeLocator
     */
    private $themeLocator;

    /**
     * That which there loads our theme yamls
     *
     * @var Forum9000\Theme\ThemeLoader
     */
    private $themeLoader;
    
    private $assetPackages;
    private $assetCtxt;
    private $assetVersioner;

    /**
     * All known themes, indexed by machine name.
     *
     * @var array
     */
    private $themes;

    public function __construct(ThemeLocator $themeLocator, ThemeLoader $themeLoader, Packages $assetPackages, VersionStrategyInterface $assetVersioner, ContextInterface $assetCtxt) {
        $this->themeLocator = $themeLocator;
        $this->themeLoader = $themeLoader;
        $this->assetPackages = $assetPackages;
        $this->assetVersioner = $assetVersioner;
        $this->assetCtxt = $assetCtxt;
    }

    private function ensure_theme_yamls() {
        if ($this->themes === null) {
            $this->themes = array();

            foreach ($this->themeLocator->discover("theme.yml") as $yaml_path) {
                $theme = $this->themeLoader->load($yaml_path);
                $this->themes[$theme->getMachineName()] = $theme;
            }
        }
    }

    public function find_theme_by_machine_name(string $machine_name) : Theme {
        $this->ensure_theme_yamls();

        return $this->themes[$machine_name];
    }

    /**
     * Given a theme, compute all paths available for one of it's resource types.
     */
    public function calculate_theme_paths(Theme $theme, string $restype) {
        $this->ensure_theme_yamls();
        
        $paths = array();
        $current_theme = $theme;
        
        if (array_key_exists($restype, $current_theme->getPaths())) {
            $paths[] = $current_theme->getThemeBasePath() . DIRECTORY_SEPARATOR . $current_theme->getPaths()[$restype];
        }
        
        while ($current_theme->getParentMachineName() !== null) {
            $current_theme = $this->find_theme_by_machine_name($current_theme->getParentMachineName());
            
            if (array_key_exists($restype, $current_theme->getPaths())) {
                $paths[] = $current_theme->getThemeBasePath() . DIRECTORY_SEPARATOR . $current_theme->getPaths()[$restype];
            }
        }

        return $paths;
    }

    /**
     * Given a theme, compute all relative URLs available for one of it's resource types.
     */
    public function calculate_theme_urls(Theme $theme, string $restype) {
        $this->ensure_theme_yamls();
        
        $paths = array();
        $current_theme = $theme;
        
        if (array_key_exists($restype, $current_theme->getPaths())) {
            $paths[] = $current_theme->getThemeUrl() . DIRECTORY_SEPARATOR . $current_theme->getPaths()[$restype];
        }
        
        while ($current_theme->getParentMachineName() !== null) {
            $current_theme = $this->find_theme_by_machine_name($current_theme->getParentMachineName());
            
            if (array_key_exists($restype, $current_theme->getPaths())) {
                $paths[] = $current_theme->getThemeUrl() . DIRECTORY_SEPARATOR . $current_theme->getPaths()[$restype];
            }
        }

        return $paths;
    }

    /**
     * Given a theme, construct a file locator for one of it's resource paths.
     */
    public function construct_theme_locator(Theme $theme, string $restype) {
        return new FileLocator($this->calculate_theme_paths($theme, $restype));
    }

    /**
     * Given a theme, construct a \Twig_Loader_Filesystem for it's templates.
     */
    public function construct_twig_loader(Theme $theme) {
        return new \Twig_Loader_Filesystem($this->calculate_theme_paths($theme, "templates"));
    }
    
    /**
     * Given a theme, construct a \Symfony\Component\Asset\PackageInterface for
     * it.
     */
    public function construct_asset_package(Theme $theme) {
        return new ThemePathPackage($this->calculate_theme_urls($theme, "assets"), $this->assetVersioner, $this->assetCtxt);
    }

    const ROUTECLASS_USER = "user";
    const ROUTECLASS_ADMIN = "admin";
    const ROUTECLASS_DEVELOPER = "developer";

    /**
     * Negotiate which theme should be used for a given request.
     *
     * @param array $arguments
     *   List of parameters that the route controller wants to be taken into
     *   account when selecting a theme.
     * @param string $routeclass
     *   Which section of the site is in use. Used to ensure admin pages have a
     *   separate theme from the rest of the site.
     * @return Theme
     *   The theme to use for this request.
     */
    public function negotiate_theme($arguments, $routeclass = ThemeRegistry::ROUTECLASS_USER) : Theme {
        if ($routeclass === ThemeRegistry::ROUTECLASS_ADMIN || $routeclass === ThemeRegistry::ROUTECLASS_DEVELOPER) {
            return $this->find_theme_by_machine_name("admin");
        }

        return $this->find_theme_by_machine_name("base");
    }

    /**
     * Apply a theme to an existing Twig environment
     * 
     * TODO: Can we have this done *before* the route executes? What happens if
     * we recursively include other route fragments? Is there a routing event
     * that can be subscribed to?
     */
    public function apply_theme(\Twig_Environment $twig, Theme $theme) {
        $theme_ldr = $twig->getLoader();
        $theme_paths = $this->calculate_theme_paths($theme, "templates");

        foreach (array_reverse($theme_paths) as $templatesPath) {
            $theme_ldr->prependPath($templatesPath);
        }

        $twig->setLoader($theme_ldr);
        
        $themeAssetPkg = $this->construct_asset_package($theme);
        $this->assetPackages->setDefaultPackage($themeAssetPkg);
    }
}
