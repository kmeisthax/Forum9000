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
        $dirres = opendir($path);
        $subdirs = array();

        while ($subdir = readdir($dirres)) {
            $subdirs[] = $subdir;
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

        return $file;
    }
}
