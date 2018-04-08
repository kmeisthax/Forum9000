<?php

namespace Forum9000\Theme;

/**
 * Service that discovers themes installed by users.
 *
 * This is how most CMSes handle extensibility, which is why we're not using,
 * say, the Symfony bundle system.
 */
class ThemeLocator {
    private $paths;

    /**
     * @param string|string[] A path or array of paths where themes can be discovered
     */
    public function __construct($paths = array()) {
        $this->paths = (array)$paths;
    }

    private function get_subdirs($path) {
        if (!@file_exists($path)) return array();

        $dirres = opendir($path);
        $subdirs = array();

        while ($subdir = readdir($dirres)) {
            if (!is_file($path . DIRECTORY_SEPARATOR . $subdir)) $subdirs[] = $path . DIRECTORY_SEPARATOR . $subdir;
        }

        closedir($dirres);

        return $subdirs;
    }

    public function discover($name) {
        $paths = array_unique($this->paths);
        $files = array();

        foreach ($paths as $path) {
            //TODO: Should we recurse the entire directory listing?
            foreach ($this->get_subdirs($path) as $discovered_dir) {
                $file = $discovered_dir . DIRECTORY_SEPARATOR . $name;
                if (@file_exists($file)) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }
}
