<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Version;

use Forum9000\Theme\ThemeRegistry;
use Forum9000\Form\ActionsType;

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
    private function create_migration_configuration(?\Closure $cl = null) {
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
        $configuration->setOutputWriter(new OutputWriter($cl));
        
        return $configuration;
    }
    
    private function get_migration_infos(Configuration $configuration, $version) {
        $infos = array();
        
        //TODO: getVersion will fail if the migration class is missing.
        //This isn't acceptable for developer console; we need to distinguish
        //between an unknown update and an update that's been applied but whose
        //migration class is missing.
        $version_obj = $configuration->getVersion($version);
        $migration = $version_obj->getMigration();
        $migration_refl = new \ReflectionClass(get_class($migration));
        
        $infos["datetime"] = $configuration->getDateTime($version);
        $infos["comment"] = $migration_refl->getDocComment();
        $infos["canup"] = $migration_refl->hasMethod("up") && !$configuration->hasVersionMigrated($version_obj);
        $infos["candown"] = $migration_refl->hasMethod("down") && $configuration->hasVersionMigrated($version_obj);
        
        $infos["uplist"] = $configuration->getMigrationsToExecute(Version::DIRECTION_UP, $version);
        $infos["downlist"] = $configuration->getMigrationsToExecute(Version::DIRECTION_DOWN, $version) + [$version_obj];
        
        return $infos;
    }
    
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
        
        $configuration = $this->create_migration_configuration();
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
    
    /**
     * @Route("/migrations/{version}", name="migration_single")
     */
    public function migration_single(Request $request, ThemeRegistry $themeReg, $version) {
        $themeReg->apply_theme($this->get("twig"), $themeReg->negotiate_theme(array(), ThemeRegistry::ROUTECLASS_DEVELOPER));
        
        $configuration = $this->create_migration_configuration();
        $migration_info = $this->get_migration_infos($configuration, $version);
        
        return $this->render("developer/migration_single.html.twig", array(
            "version" => $version,
            "migration_info" => $migration_info
        ));
    }
    
    /**
     * @Route("/migrations/{version}/{exec_action}", name="migration_execute")
     */
    public function migration_execute(Request $request, ThemeRegistry $themeReg, $version, $exec_action) {
        $themeReg->apply_theme($this->get("twig"), $themeReg->negotiate_theme(array(), ThemeRegistry::ROUTECLASS_DEVELOPER));
        
        $messages = array();

        $configuration = $this->create_migration_configuration(function ($msg) use ($messages) {
            $messages[] = $msg;
        });
        $migration_info = $this->get_migration_infos($configuration, $version);
        
        //Create a form with buttons to click
        $actions_form = $this->createForm(ActionsType::class, null, array(
            "actions" => array(
                "trial" => "Test migration",
                "confirm" => "Execute migration",
                "cancel" => "Cancel"
            )
        ));
        $actions_form->handleRequest($request);
        
        if ($actions_form->isSubmitted() && $actions_form->isValid()) {
            if ($actions_form->getClickedButton()->getName() == "cancel") {
                return $this->redirectToRoute("f9kdeveloper_migration_single", array("version" => $version));
            }
            
            $dryrun = $actions_form->getClickedButton()->getName() == "trial";

            switch ($exec_action) {
                case "up":
                case "down":
                    //TODO: Actually log what happened.
                    foreach ($migration_info[$exec_action . "list"] as $interim_ver) {
                        $messages[] = $interim_ver->getVersion();

                        $interim_ver->execute($exec_action, $dryrun, true);
                    }
                    return new Response(implode("<p>", $messages));
                default:
                    throw new Exception("Invalid action");
            }
        }
        
        return $this->render("developer/migration_execute.html.twig", array(
            "version" => $version,
            "migration_info" => $migration_info,
            "exec_action" => $exec_action,
            "actions_form" => $actions_form->createView(),
        ));
    }
}
