<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Version;

use Forum9000\Form\SiteEnvType;
use Forum9000\Form\ActionsType;
use Forum9000\Form\RegistrationType;
use Forum9000\Theme\Annotation\Theme;
use Forum9000\Entity\User;

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
 * @Route("/install", name="f9kinstall_")
 * @Theme(routeClass="developer")
 */
class InstallController extends Controller {
    use \Forum9000\OnsiteDatabaseAdmin\DBAControllerTrait;

    /**
     * @Route("/", name="environment")
     */
    function environment(Request $req) {
        $kernel = $this->get('kernel');

        if ($kernel->isInstalled()) {
            throw new \Exception('Forum configuration is already installed.');
        }
        
        if ($kernel->isEnvironmentConfigured()) {
            return $this->redirectToRoute("f9kinstall_database");
        }
        
        $form = $this->createForm(SiteEnvType::class);
        $form->handleRequest($req);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $env_vars = $form->getData();
            
            //This is how the Symfony template app env generator does it
            if (function_exists('openssl_random_pseudo_bytes')) {
                $env_vars["APP_SECRET"] = hash('sha1', openssl_random_pseudo_bytes(23));
            }
            $env_vars["APP_SECRET"] = hash('sha1', uniqid(mt_rand(), true));
            
            $env_lines = [];
            
            foreach ($env_vars as $k => $v) {
                $has_quote_single = strpos($v, "'");
                $has_quote_double = strpos($v, '"');
                
                if ($has_quote_single && $has_quote_double) {
                    $esc_v = '"' . str_replace("'", "\\'", $v) . '"';
                } else if ($has_quote_single) {
                    $esc_v = '"' . $v . '"';
                } else if ($has_quote_double) {
                    $esc_v = "'" . $v . "'";
                } else {
                    $esc_v = $v;
                }
                
                $env_lines[] = $k . "=" . $esc_v;
            }
            
            $env_lines[] = "";
            $env_lines[] = "#Please remove once install has completed.";
            $env_lines[] = "F9K_NOT_INSTALLED=true";
            
            $env = implode("\n", $env_lines);
            $kernel->writeDotEnv($env);
            
            return $this->redirectToRoute("f9kinstall_database");
        }

        return $this->render("install/environment_stage.html.twig", array(
            "environment_form" => $form->createView(),
        ));
    }
    
    /**
     * @Route("/database", name="database")
     */
    function database(Request $req) {
        $kernel = $this->get('kernel');

        if ($kernel->isInstalled()) {
            throw new \Exception('Forum configuration is already installed.');
        }
        
        $database_exists = $this->check_database_exists();
        $database_migration_needed = true;
        $migration_count = 0;
        $actions = array();
        if (!$database_exists) $actions["create"] = "Create database";

        $migration_conf = $this->create_migration_configuration();

        $pending_migrations = array();

        foreach ($migration_conf->getAvailableVersions() as $order => $name) {
            $pending_migrations[$name] = true;
        }

        if ($database_exists) {
            foreach ($migration_conf->getMigratedVersions() as $order => $name) {
                unset($pending_migrations[$name]);
            }
        }

        $migration_count = count($pending_migrations);
        $database_migration_needed = $migration_count > 0;
        if ($database_exists && $database_migration_needed) $actions["upgrade"] = "Run upgrades";
        
        if ($database_exists && !$database_migration_needed) {
            return $this->redirectToRoute("f9kinstall_owner_registration");
        }

        $actions_form = $this->createForm(ActionsType::class, null, array("actions" => $actions));
        $actions_form->handleRequest($req);
        if ($actions_form->isSubmitted() && $actions_form->isValid()) {
            switch ($actions_form->getClickedButton()->getName()) {
                case "create":
                    $this->create_empty_database();
                    return $this->redirectToRoute("f9kinstall_database");
                case "upgrade":
                    if (!$database_exists) throw new \Exception("Cannot upgrade database until database exists");

                    $migration_info = $this->get_migration_infos($migration_conf, array_keys($pending_migrations)[$migration_count - 1]);
                    foreach ($migration_info["uplist"] as $interim_ver) {
                        $interim_ver->execute("up", false, true);
                    }

                    return $this->redirectToRoute("f9kinstall_database");
                default:
                    throw new \Exception("Action " . $actions_form->getClickedButton()->getName() . " does not exist.");
            }
        }
        
        return $this->render("install/database_stage.html.twig", array(
            "database_exists" => $database_exists,
            "database_migration_needed" => $database_migration_needed,
            "migration_count" => $migration_count,
            "actions_form" => $actions_form->createView()
        ));
    }

    /**
     * @Route("/owner_registration", name="owner_registration")
     */
    function owner_registration(Request $req, UserPasswordEncoderInterface $encoder) {
        $kernel = $this->get('kernel');

        if ($kernel->isInstalled()) {
            throw new \Exception('Forum configuration is already installed.');
        }
        
        $em = $this->getDoctrine()->getManager();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        
        if ($userRepo->getUserRoleCount(User::DEVELOPER) > 0) {
            return $this->redirectToRoute("f9kinstall_finalize");
        }
        
        $user = new User();
        $user->setSiteRole(User::DEVELOPER);
        
        $form = $this->createForm(RegistrationType::class);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

            $em->persist($user);
            $em->flush();
        }
        
        return $this->render("install/owner_registration_stage.html.twig", array(
            "registration_form" => $form->createView(),
        ));
    }

    /**
     * @Route("/finalize", name="finalize")
     */
    function finalize(Request $req) {
        $kernel = $this->get('kernel');

        if ($kernel->isInstalled()) {
            return $this->redirectToRoute('f9kforum_homepage');
        }
        
        $lines = [];
        $install_blocker_removable = false;
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $kernel->readDotEnv()) as $line) {
            if (strpos($line, "F9K_NOT_INSTALLED=") === 0) {
                $lines[] = "#F9K_NOT_INSTALLED line removed";
                $install_blocker_removable = true;
            } else {
                $lines[] = $line;
            }
        }
        
        $actions = array();
        if ($install_blocker_removable) $actions["lock"] = "Finish installation";
        
        $form = $this->createForm(ActionsType::class, null, array("actions" => $actions));
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($install_blocker_removable) {
                $env = implode("\n", $lines);
                $kernel->writeDotEnv($env);

                return $this->redirectToRoute('f9kinstall_finalize');
            } else {
                throw new \Exception("Install blocker environment variable not removable but request was sent to remove it.");
            }
        }
        
        return $this->render("install/finalize_stage.html.twig", array(
            "install_blocker_removable" => $install_blocker_removable,
            "actions_form" => $form->createView()
        ));
    }
}
