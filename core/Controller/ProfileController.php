<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Forum9000\Entity\User;
use Forum9000\Entity\Group;
use Forum9000\Entity\Actor;

use Forum9000\Theme\Annotation\Theme;

/**
 * Controller which renders user and group pages.
 *
 * @Route(name="f9kprofile_")
 * @Theme(routeClass="user")
 */
class ProfileController extends Controller {
    /**
     * @Route("/user/{handle}", name="user")
     */
    function user_single(Request $request, $handle) {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->findOneByHandle($handle);

        return $this->render("profile/user_single.html.twig", array("user" => $user));
    }
}
