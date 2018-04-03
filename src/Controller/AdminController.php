<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Criteria;

use App\Entity\Forum;
use App\Form\ForumType;

class AdminController extends Controller {
    /**
     * @Route("/admin/forums")
     */
    public function forums(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $forumRepo = $this->getDoctrine()->getRepository(Forum::class);

        $forum = new Forum();
        $user = $this->getUser();

        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $forum = $form->getData();

            $em->persist($forum);
            $em->flush();
        }

        $forums = $forumRepo->findAll();

        return $this->render(
                                "admin/forums.html.twig",
                                array(
                                    "forums" => $forums,
                                    "forum_form" => $form->createView()
                                )
                            );
    }
}
