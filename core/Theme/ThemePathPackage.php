<?php

namespace Forum9000\Theme;

use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\Asset\Context\ContextInterface;

/**
 * Implementation of PackageInterface for theme assets.
 * 
 * A ThemePathPackage acts like a PathPackage pointing at multiple directories.
 * Files that don't exist in the current theme will fall back to those in parent
 * themes, allowing child overrides to work.
 */
class ThemePathPackage extends Package {
    /**
     * @var array
     */
    private $basePaths;
    
    /**
     * @param array $basePaths
     *   The list of base paths to check for a particular relative asset.
     * @param VersionStrategyInterface $versionStrategy The version strategy
     * @param ContextInterface|null    $context         The context
     */
    public function __construct(array $basePaths, VersionStrategyInterface $versionStrategy, ContextInterface $context = null) {
        parent::__construct($versionStrategy, $context);
        
        $this->basePaths = array();
        
        foreach ($basePaths as $basePath) {
            if (!$basePath) {
                $this->basePaths[] = '/';
            } else {
                if ('/' != $basePath[0]) {
                    $basePath = '/'.$basePath;
                }
                
                $this->basePaths[] = rtrim($basePath, '/').'/';
            }
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function getUrl($path) {
        if ($this->isAbsoluteUrl($path)) return $path;
        
        $versionedPath = $this->getVersionStrategy()->applyVersion($path);
        if ($this->isAbsoluteUrl($versionedPath) || ($versionedPath && '/' === $versionedPath[0])) return $versionedPath;
        
        foreach ($this->basePaths as $basePath) {
            $localPath = getcwd() . DIRECTORY_SEPARATOR . $basePath . DIRECTORY_SEPARATOR . $path;
            $remoteUrl = $this->getContext()->getBasePath() . $basePath . ltrim($versionedPath, '/');
            
            if (@file_exists($localPath)) {
                return $remoteUrl;
            }
        }
    }
}