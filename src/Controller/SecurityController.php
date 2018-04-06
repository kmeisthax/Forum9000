<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\User;
use App\Form\RegistrationType;

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
