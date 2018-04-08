<?php

namespace Forum9000\Theme;

use Symfony\Component\Config\FileLocator;

/**
 * Storage for loaded theme configuration & such.
 *
 * Also, responsible for determining the override hierarchy for a given theme.
 */
class ThemeRegistry {
    /**
     * That which finds our theme yamls
     *
     * @var Symfony\Component\Config\FileLocator
     */
    private $themeLocator;

    /**
     * That which there loads our theme yamls
     *
     * @var Forum9000\Theme\ThemeLoader
     */
    private $themeLoader;

    /**
     * All known themes, indexed by machine name.
     *
     * @var array
     */
    private $themes;

    public function __construct(ThemeLocator $themeLocator, ThemeLoader $themeLoader) {
        $this->themeLocator = $themeLocator;
        $this->themeLoader = $themeLoader;
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

        $paths = array($theme->getThemeBasePath() . DIRECTORY_SEPARATOR . $theme->getPaths()[$restype]);
        $current_theme = $theme;

        while ($current_theme->getParentMachineName() !== null) {
            $current_theme = $this->find_theme_by_machine_name($current_theme->getParentMachineName());

            $paths[] = $current_theme->getThemeBasePath() . DIRECTORY_SEPARATOR . $current_theme->getPaths()[$restype];
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

    const ROUTECLASS_USER = "user";
    const ROUTECLASS_ADMIN = "admin";

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
        if ($routeclass === ThemeRegistry::ROUTECLASS_ADMIN) {
            return $this->find_theme_by_machine_name("admin");
        }

        return $this->find_theme_by_machine_name("base");
    }

    /**
     * Apply a theme to an existing Twig environment
     */
    public function apply_theme(\Twig_Environment $twig, Theme $theme) {
        $theme_ldr = $twig->getLoader();
        $theme_paths = $this->calculate_theme_paths($theme, "templates");

        foreach (array_reverse($theme_paths) as $templatesPath) {
            $theme_ldr->prependPath($templatesPath);
        }

        $twig->setLoader($theme_ldr);
    }
}
