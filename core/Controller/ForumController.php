<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Criteria;

use Forum9000\Entity\Forum;
use Forum9000\Entity\Thread;
use Forum9000\Entity\Post;
use Forum9000\Form\PostType;
use Forum9000\Theme\ThemeRegistry;

/**
 * All routes having to do with a forum.
 *
 * @Route(name="f9kforum_")
 */
class ForumController extends Controller {
    /**
     * @Route("/forum/{id}", name="forum")
     */
    public function forum(Request $request, ThemeRegistry $themeReg, $id) {
        $em = $this->getDoctrine()->getManager();
        $forumRepo = $this->getDoctrine()->getRepository(Forum::class);
        $threadRepo = $this->getDoctrine()->getRepository(Thread::class);
        
        $forum = $forumRepo->findByCompactId($id);
        $this->denyAccessUnlessGranted('view', $forum);
        
        $themeReg->apply_theme($this->get("twig"), $themeReg->negotiate_theme(array(), ThemeRegistry::ROUTECLASS_USER));

        $post = new Post();
        $user = $this->getUser();
        
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('post', $forum);

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

            return $this->redirectToRoute("f9kforum_thread", array("id" => $thread->getCompactId()));
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
     * @Route("/thread/{id}", name="thread")
     */
    public function thread(Request $request, ThemeRegistry $themeReg, $id) {
        $em = $this->getDoctrine()->getManager();
        $threadRepo = $this->getDoctrine()->getRepository(Thread::class);

        $thread = $threadRepo->findByCompactId($id);
        $this->denyAccessUnlessGranted('view', $thread);

        $themeReg->apply_theme($this->get("twig"), $themeReg->negotiate_theme(array(), ThemeRegistry::ROUTECLASS_USER));

        $reply = new Post();
        $user = $this->getUser();

        $form = $this->createForm(PostType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('reply', $thread);

            $reply = $form->getData();

            $reply->setThread($thread);
            $reply->setOrder($thread->getNewestPosts()[0]->getOrder() + 1);
            $thread->getPosts()->add($reply);
            
            if ($user !== null) $reply->setPostedBy($user);
            $reply->setCtime(new \DateTime());

            $em->persist($reply);
            $em->flush();

            return $this->redirectToRoute("f9kforum_thread", array("id" => $thread->getCompactId()));
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
