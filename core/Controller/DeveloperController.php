<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Forum9000\Theme\Annotation\Theme;
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
 * @Theme(routeClass="developer")
 */
class DeveloperController extends Controller {
    use \Forum9000\OnsiteDatabaseAdmin\DBAControllerTrait;
    
    /**
     * @Route("/", name="dashboard")
     */
    public function dashboard(Request $request) {
        $configuration = $this->create_migration_configuration();

        $available_migration_count = $configuration->getNumberOfAvailableMigrations();
        $executed_migration_count = $configuration->getNumberOfExecutedMigrations();
        $pending_migration_count = $available_migration_count - $executed_migration_count;

        return $this->render("developer/dashboard.html.twig", array(
            "pending_migration_count" => $pending_migration_count
        ));
    }

    /**
     * Migration console
     * 
     * Allows developers to check the database's migration status (if
     * applicable) and manually execute a migration if necessary.
     * 
     * @Route("/migrations", name="migrations")
     */
    public function migrations(Request $request) {
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
    public function migration_single(Request $request, $version) {
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
    public function migration_execute(Request $request, $version, $exec_action) {
        $messages = array();

        $configuration = $this->create_migration_configuration(function ($msg) use (&$messages) {
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
                        $interim_ver->execute($exec_action, $dryrun, true);
                    }

                    return $this->render("developer/migration_log.html.twig", array(
                        "version" => $version,
                        "migration_info" => $migration_info,
                        "exec_action" => $exec_action,
                        "messages" => implode(",", $messages),
                    ));
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
