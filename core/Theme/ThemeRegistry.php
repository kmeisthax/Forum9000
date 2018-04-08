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
     * Given a theme, construct a file locator for one of it's resource paths.
     */
    public function construct_theme_locator(Theme $theme, string $restype) {
        $this->ensure_theme_yamls();

        $paths = array($theme->getPaths()[$restype]);
        $current_theme = $theme;

        while ($current_theme->getParentMachineName() !== null) {
            $current_theme = $this->find_theme_by_machine_name($current_theme->getParentMachineName());

            array_push($current_theme->getPaths()[$restype], $paths);
        }

        return new FileLocator($paths);
    }
}
