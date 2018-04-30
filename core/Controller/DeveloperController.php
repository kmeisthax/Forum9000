<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\DBAL\Migrations\Configuration\Configuration;

use Forum9000\Theme\ThemeRegistry;

/**
 * The most sensitive console exposed by Forum9000.
 * 
 * The AdminController is for things only paid staff operating the site should
 * have access to. That's why it's locked behind ROLE_STAFF and doesn't ask for
 * permission before making changes. DeveloperController is like that but one
 * level up. It's locked behind ROLE_DEVELOPER and allows direct database access
 * and other functionality that only site developers should use. It is very easy
 * to brick or wipe a Forum9000 install by misusing the developer console and
 * thus only engineering staff should have access to it.
 * 
 * It is also specifically designed to remain usable even if the entire rest of
 * the website isn't. SQL queries are kept to a minimum and explicitly allowed
 * to fail to ensure whatever data does exist can be inspected. As a result,
 * only a handful of functions actually exist here; developers should use the
 * Developer console only to fix a broken site and then the Admin console for
 * their normal staff duties.
 * 
 * @Route("/admin/developer", name="f9kdeveloper_")
 */
class DeveloperController extends Controller {
    /**
     * Migration console
     * 
     * Allows developers to check the database's migration status (if
     * applicable) and manually execute a migration if necessary.
     * 
     * @Route("/migrations", name="migrations")
     */
    public function migrations(Request $request, ThemeRegistry $themeReg) {
        $themeReg->apply_theme($this->get("twig"), $themeReg->negotiate_theme(array(), ThemeRegistry::ROUTECLASS_DEVELOPER));
        
        $container = $this->container;
        $connection = $this->get("doctrine")->getConnection();
        $dir = $container->getParameter('doctrine_migrations.dir_name');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        
        $configuration = new Configuration($connection);
        $configuration->setMigrationsNamespace($container->getParameter('doctrine_migrations.namespace'));
        $configuration->setMigrationsDirectory($dir);
        $configuration->registerMigrationsFromDirectory($dir);
        $configuration->setName($container->getParameter('doctrine_migrations.name'));
        $configuration->setMigrationsTableName($container->getParameter('doctrine_migrations.table_name'));
        
        $migrations = array();
        
        foreach ($configuration->getAvailableVersions() as $order => $name) {
            $migrations[$name] = array(
                "version" => $name,
                "applied" => false,
                "current" => false,
                "available" => true,
            );
        }
        
        foreach ($configuration->getMigratedVersions() as $order => $name) {
            if (array_key_exists($name, $migrations)) {
                $migrations[$name]["applied"] = true;
            } else {
                $migrations[$name] = array(
                    "version" => $name,
                    "applied" => true,
                    "current" => false,
                    "available" => false,
                );
            }
        }
        
        $migrations[$configuration->getCurrentVersion()]["current"] = true;
        
        krsort($migrations);
        
        return $this->render("developer/migrations.html.twig", array(
            "migrations" => $migrations
        ));
    }
}