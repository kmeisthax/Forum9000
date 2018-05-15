<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
        if ($this->getUser() !== null) {
            return $this->redirectToRoute("f9kforum_homepage");
        }
        
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
    public function register(Request $request, UserPasswordEncoderInterface $encoder, TokenStorageInterface $token_storage, SessionInterface $session) {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute("f9kforum_homepage");
        }
        
        $em = $this->getDoctrine()->getManager();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = new User();
        $user->setSiteRole(User::USER);

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

            $em->persist($user);
            $em->flush();

            $token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
            $token_storage->setToken($token);
            $session->set('_security_main', serialize($token));

            return $this->redirectToRoute('f9kuser_login');
        }

        return $this->render('security/register.html.twig', array(
            'register_form' => $form->createView()
        ));
    }
}
