<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Version;

use Forum9000\Form\SiteEnvType;
use Forum9000\Theme\Annotation\Theme;

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
    /**
     * @Route("/", name="environment")
     */
    function environment(Request $req) {
        $kernel = $this->get('kernel');

        if ($kernel->isInstalled()) {
            throw new \Exception('Forum configuration is already installed.');
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
        
        //Determine if database exists or not
        
        return $this->render("install/database_stage.html.twig", array(
            "environment_form" => $form->createView(),
        ));
    }
}
