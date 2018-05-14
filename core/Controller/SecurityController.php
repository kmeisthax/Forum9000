<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Forum9000\Entity\User;
use Forum9000\Form\RegistrationType;
use Forum9000\Theme\Annotation\Theme;

/**
 * @Route(name="f9kuser_")
 * @Theme(routeClass="user")
 */
class SecurityController extends Controller {
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $auth) {
        // get the login error if there is one
        $error = $auth->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $auth->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder) {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = new User();
        $user->setSiteRole(User::USER);

        //First user registration gets developer role
        //TODO: This fails if === is in use. Can we fix the count methods to
        //return integers?
        if ($userRepo->getUserCount() == 0) $user->setSiteRole(User::DEVELOPER);

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('f9kuser_login');
        }

        return $this->render('security/register.html.twig', array(
            'register_form' => $form->createView()
        ));
    }
}
