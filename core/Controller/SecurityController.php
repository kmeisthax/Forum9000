<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Forum9000\Entity\User;
use Forum9000\Form\RegistrationType;
use Forum9000\Theme\ThemeRegistry;

class SecurityController extends Controller {
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, ThemeRegistry $themeReg, AuthenticationUtils $auth) {
        $themeReg->apply_theme($this->get("twig"), $themeReg->negotiate_theme(array(), ThemeRegistry::ROUTECLASS_USER));

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
    public function register(Request $request, ThemeRegistry $themeReg, UserPasswordEncoderInterface $encoder) {
        $themeReg->apply_theme($this->get("twig"), $themeReg->negotiate_theme(array(), ThemeRegistry::ROUTECLASS_USER));

        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $user->setSiteRole(User::USER);

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

            $em->persist($user);
            $em->flush();
        }

        return $this->render('security/register.html.twig', array(
            'register_form' => $form->createView()
        ));
    }
}
