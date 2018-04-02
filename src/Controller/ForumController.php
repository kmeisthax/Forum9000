<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Criteria;

use App\Entity\Thread;
use App\Entity\Post;
use App\Form\PostType;

class ForumController extends Controller {
    /**
     * @Route("/")
     */
    public function forum(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $threadRepo = $this->getDoctrine()->getRepository(Thread::class);

        $post = new Post();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();

            $thread = new Thread();
            $post->setThread($thread);
            $post->setOrder(0);
            $thread->getPosts()->add($post);

            $em->persist($post);
            $em->persist($thread);
            $em->flush();
        }

        $threads = $threadRepo->findAll();

        return $this->render(
                                "forum/forum.html.twig",
                                array(
                                    "threads" => $threads,
                                    "thread_form" => $form->createView()
                                )
                            );
    }
}
