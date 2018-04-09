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
     * 
     * @return boolean
     *   True if all filenames in the asset can be compiled by this compiler.
     */
    public function canCompileAsset(array $filenames) : boolean;
    
    /**
     * Compile a given set of assets, returning an array of strings representing
     * compiled result files.
     * 
     * @param array $filenames
     *   Array of file contents indexed by filenames. Passing multiple files
     *   into an asset compiler is supported, with the caveat that the asset
     *   compiler will treat each file in a compiler-specific manner. Generally
     *   however, files of the same type will be aggregated into a single file;
     *   files which constitute source maps will be replaced with updated source
     *   maps; and so on and so forth.
     * @param Forum9000\Theme\FileLocator $themeFiles
     *   A FileLocator to load said files from. Must include the theme resource
     *   path that the $filenames are relative to. This will also be used to
     *   load any additional resources referenced by the files.
     * 
     * @return array
     *   Array of file contents indexed by filenames, representing the target
     *   form of the transformation.
     */
    public function compileAssetToFile(array $files, FileLocator $themeFiles) : array;
}