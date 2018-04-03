<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Criteria;

use App\Entity\Forum;
use App\Entity\Thread;
use App\Entity\Post;
use App\Form\PostType;

class ForumController extends Controller {
    /**
     * @Route("/forum/{id}")
     */
    public function forum(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $forum = $em->find("App\Entity\Forum", $id);
        $threadRepo = $this->getDoctrine()->getRepository(Thread::class);
        
        $post = new Post();
        $user = $this->getUser();
        
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();

            $thread = new Thread();
            $forum->getThreads()->add($thread);
            $thread->setForum($forum);
            
            $post->setThread($thread);
            $post->setOrder(0);
            $thread->getPosts()->add($post);
            
            if ($user !== null) $post->setPostedBy($user);
            $post->setCtime(new \DateTime());

            $em->persist($post);
            $em->persist($thread);
            $em->flush();
        }
        
        $threads = $threadRepo->getLatestThreadsInForum($forum, 0, 10);
        
        return $this->render(
                                "forum/forum.html.twig",
                                array(
                                    "forum" => $forum,
                                    "threads" => $threads,
                                    "thread_form" => $form->createView()
                                )
                            );
    }
    
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
            
            if ($user !== null) $post->setPostedBy($user);
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
