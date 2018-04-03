<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Thread;
use App\Entity\Post;
use App\Form\PostType;

class ThreadController extends Controller {
    /**
     * @Route("/thread/{id}")
     */
    public function thread(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $thread = $em->find("App\Entity\Thread", $id);

        $reply = new Post();
        $user = $this->getUser();

        $form = $this->createForm(PostType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reply = $form->getData();

            $reply->setThread($thread);
            $reply->setOrder($thread->getNewestPosts()[0]->getOrder() + 1);
            $thread->getPosts()->add($reply);
            
            $reply->setPostedBy($user);
            $reply->setCtime(new \DateTime());

            $em->persist($reply);
            $em->flush();
        }

        $posts = $thread->getOrderedPosts(0, 20);

        return $this->render(
                                "forum/thread.html.twig",
                                array(
                                    "thread" => $thread,
                                    "posts" => $posts,
                                    "reply_form" => $form->createView()
                                )
                            );
    }
}
