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
     * @Route("/")
     */
    function install(Request $req) {
        $kernel = $this->get('kernel');

        if ($kernel->isInstalled()) {
            throw new \Exception('Forum configuration is already installed.');
        }

        $form = $this->createForm(SiteEnvType::class);
        $form->handleRequest($req);

        return $this->render("install/database_stage.html.twig", array(
            "environment_form" => $form->createView(),
        ));
    }
}
