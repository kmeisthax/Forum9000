<?php

namespace Forum9000\Theme;

use Symfony\Component\Config\FileLocator;

/**
 * Interface for things that compile scripts, stylesheets, and so on...
 */
interface AssetCompilerInterface {
    /**
     * Determines if the asset compiler can compile a particular group of files
     * into a given asset.
     * 
     * @param array $filenames
     *   The list of files to compile. Files must be relative to a given theme
     *   resource path.
     */
    public function canCompileAsset(array $filenames) : boolean;
    
    /**
     * Compile a given set of assets, returning an array of strings representing
     * compiled result files.
     * 
     * @param array $filenames
     *   The list of files to compile. Files must be relative to a given theme
     *   resource path.
     * @param Forum9000\Theme\FileLocator $themeFiles
     *   A FileLocator to load said files from. Must include the theme resource
     *   path that the $filenames are relative to. This will also be used to
     *   load any additional resources referenced by the files.
     */
    public function compileAssetToFile(array $filenames, FileLocator $themeFiles) : array;
}