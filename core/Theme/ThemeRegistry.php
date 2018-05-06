<?php

namespace Forum9000\Theme;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Doctrine\Common\Annotations\Reader;

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
    
    private $requestStack;
    private $controllerResolver;
    private $annotationReader;

    /**
     * All known themes, indexed by machine name.
     *
     * @var array
     */
    private $themes;
    
    private $default_themes;

    public function __construct(ThemeLocator $themeLocator, ThemeLoader $themeLoader, Packages $assetPackages, VersionStrategyInterface $assetVersioner, ContextInterface $assetCtxt, RequestStack $requestStack, ControllerResolverInterface $controllerResolver, Reader $annotationReader, array $default_themes) {
        $this->themeLocator = $themeLocator;
        $this->themeLoader = $themeLoader;
        $this->assetPackages = $assetPackages;
        $this->assetVersioner = $assetVersioner;
        $this->assetCtxt = $assetCtxt;
        $this->requestStack = $requestStack;
        $this->controllerResolver = $controllerResolver;
        $this->annotationReader = $annotationReader;
        $this->default_themes = $default_themes;
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
    public function extend_twig_loader(Theme $theme, \Twig_Loader_Filesystem $tlfs) {
        //Symfony defines it's own main-namespace templates, so we need to
        //ensure they remain in all theme namespaces, too.
        $legacyPaths = $tlfs->getPaths();

        foreach ($this->calculate_theme_paths($theme, "templates") as $path) $tlfs->addPath($path);

        //Create a separate namespace for each theme in the chain
        $current_theme = $theme;
        $tlfs->setPaths($this->calculate_theme_paths($current_theme, "templates") + $legacyPaths, $current_theme->getMachineName());

        while ($current_theme->getParentMachineName() !== null) {
            $current_theme = $this->find_theme_by_machine_name($current_theme->getParentMachineName());
            $tlfs->setPaths($this->calculate_theme_paths($current_theme, "templates") + $legacyPaths, $current_theme->getMachineName());
        };

        return $tlfs;
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
     * @return Theme
     *   The theme to use for this request.
     */
    public function negotiate_theme() : Theme {
        $request = $this->requestStack->getCurrentRequest();
        $controller = $this->controllerResolver->getController($request);
        if (is_array($controller) && count($controller) > 1) {
            $class = $controller[0];
            $method = $controller[1];
        } else if (is_array($controller)) {
            $method = $controller[0];
        } else {
            $method = $controller;
        }
        
        if (isset($class)) {
            $theme_class_annotation = $this->annotationReader->getClassAnnotation(new \ReflectionClass($class), 'Forum9000\Theme\Annotation\Theme');
            $theme_method_annotation = $this->annotationReader->getMethodAnnotation(new \ReflectionMethod($class, $method), 'Forum9000\Theme\Annotation\Theme');
            
            if ($theme_method_annotation) {
                $routeclass = $theme_method_annotation->getRouteClass();
            } else if ($theme_class_annotation) {
                $routeclass = $theme_class_annotation->getRouteClass();
            } else {
                $routeclass = "user";
            }
        } else {
            //Doctrine can't read annotations on loose functions, so...
            $routeclass = "user";
        }
        
        $default_theme = $this->default_themes[$routeclass];
        
        return $this->find_theme_by_machine_name($default_theme);
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
        $this->extend_twig_loader($theme, $theme_ldr);
        $twig->setLoader($theme_ldr);
        
        $themeAssetPkg = $this->construct_asset_package($theme);
        $this->assetPackages->setDefaultPackage($themeAssetPkg);
    }
}
