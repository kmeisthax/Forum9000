<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Forum9000\Entity\User;
use Forum9000\Entity\Group;
use Forum9000\Entity\Actor;

use Forum9000\Theme\ThemeRegistry;

/**
 * Controller which renders user and group pages.
 *
 * @Route(name="f9kprofile_")
 */
class ProfileController extends Controller {
    /**
     * @Route("/user/{user_id}", name="user")
     */
    function user_single(Request $request, ThemeRegistry $themeReg, $user_id) {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->findByCompactId($user_id);

        $themeReg->apply_theme($this->get("twig"), $themeReg->negotiate_theme(array(), ThemeRegistry::ROUTECLASS_USER));

        return $this->render("profile/user_single.html.twig", array("user" => $user));
    }
}
