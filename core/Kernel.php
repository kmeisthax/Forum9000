<?php

namespace Forum9000;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

use Forum9000\MarkupLanguage\MarkupLanguageInterface;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    const CONFIG_EXTS = '.{php,xml,yaml,yml}';
    
    /**
     * Determine if environment has been configured.
     * 
     * This does not necessarily equivocate to having a .env file.
     */
    public function isEnvironmentConfigured() {
        return isset($_SERVER["APP_SECRET"]);
    }

    /**
     * Determine if the application has been installed.
     *
     * If the application is not installed, then any user will be allowed to
     * hit /install to configure .env and create the owner account for the
     * forum.
     */
    public function isInstalled() {
        return file_exists($this->getProjectDir().'/.env') && !(isset($_SERVER["F9K_NOT_INSTALLED"]) && $_SERVER["F9K_NOT_INSTALLED"] == "true");
    }
    
    public function readDotEnv() : string {
        return file_get_contents($this->getProjectDir() . '/.env');
    }
    
    /**
     * Write a .env file to disk.
     */
    public function writeDotEnv($env_contents) {
        file_put_contents($this->getProjectDir() . '/.env', $env_contents);
    }

    public function getProjectDir() {
        return realpath(__DIR__.'/../');
    }

    public function getCacheDir() {
        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    public function getLogDir() {
        return $this->getProjectDir().'/var/log';
    }

    public function registerBundles() {
        $contents = require $this->getProjectDir().'/core/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->addResource(new FileResource($this->getProjectDir().'/core/bundles.php'));
        // Feel free to remove the "container.autowiring.strict_mode" parameter
        // if you are using symfony/dependency-injection 4.0+ as it's the default behavior
        $container->setParameter('container.autowiring.strict_mode', true);
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().'/core/config';

        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $confDir = $this->getProjectDir().'/core/config';

        $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}'.self::CONFIG_EXTS, '/', 'glob');
    }
    
    protected function build(ContainerBuilder $cb) {
        $cb->registerForAutoconfiguration(MarkupLanguageInterface::class)->addTag('forum9000.markup_language');
    }
}
