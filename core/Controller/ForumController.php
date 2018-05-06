<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Criteria;

use Forum9000\Entity\Forum;
use Forum9000\Entity\Thread;
use Forum9000\Entity\Post;
use Forum9000\Form\PostType;
use Forum9000\Form\LockType;
use Forum9000\Theme\Annotation\Theme;

/**
 * All routes having to do with a forum.
 *
 * @Route(name="f9kforum_")
 * @Theme(routeClass="user")
 */
class ForumController extends Controller {
    /**
     * @Route("/", name="homepage")
     */
    function homepage(Request $req) {
        $em = $this->getDoctrine()->getManager();
        $forumRepo = $this->getDoctrine()->getRepository(Forum::class);
        
        $children = $forumRepo->findAllRootforums();
        
        return $this->render(
            'forum/homepage.html.twig',
            array(
                "children" => $children
            )
        );
    }
    
    /**
     * @Route("/forum/{slug}/{page}", name="forum", requirements={"page" = "\d+"})
     */
    public function forum(Request $request, Forum $forum, $page=1) {
        $em = $this->getDoctrine()->getManager();
        $forumRepo = $this->getDoctrine()->getRepository(Forum::class);
        
        $this->denyAccessUnlessGranted('view', $forum);

        $post = new Post();
        $user = $this->getUser();
        
        $children = $forumRepo->getForumChildren($forum, ($page - 1) * 10, 10);
        $children_count = $forumRepo->getForumChildCount($forum);
        
        return $this->render(
                                "forum/forum.html.twig",
                                array(
                                    "forum" => $forum,
                                    "page" => $page,
                                    "children" => $children,
                                    "children_count" => $children_count
                                )
                            );
    }
    
    /**
     * @Route("/forum/{slug}/post", name="forum_post")
     */
    public function forum_post(Request $request, Forum $forum) {
        $em = $this->getDoctrine()->getManager();
        
        $this->denyAccessUnlessGranted('post', $forum);

        $post = new Post();
        $user = $this->getUser();
        
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();

            $thread = new Thread();
            $thread->setIsLocked(false);
            $thread->setOrder(0);
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

            return $this->redirectToRoute("f9kforum_thread", array("forum_slug" => $forum->getSlug(), "id" => $thread->getCompactId()));
        }
        
        return $this->render(
                                "forum/forum_post.html.twig",
                                array(
                                    "forum" => $forum,
                                    "thread_form" => $form->createView()
                                )
                            );
    }
    
    /**
     * @Route("/forum/{forum_slug}/{id}/{page}", name="thread", requirements={"page" = "\d+"})
     * @ParamConverter("forum", options={"mapping" = {"forum_slug": "slug"}})
     * @Entity("thread", expr="repository.findByCompactId(id)")
     */
    public function thread(Request $request, Forum $forum, Thread $thread, $page = 1) {
        $em = $this->getDoctrine()->getManager();
        $postRepo = $this->getDoctrine()->getRepository(Post::class);
        
        $this->denyAccessUnlessGranted('view', $thread);

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

            return $this->redirectToRoute("f9kforum_thread", array("forum_slug" => $forum->getSlug(), "id" => $thread->getCompactId()));
        }

        $posts = $thread->getOrderedPosts(($page - 1) * 20, 20);
        $post_count = $postRepo->getThreadPostCount($thread);

        return $this->render(
                                "forum/thread.html.twig",
                                array(
                                    "forum" => $forum,
                                    "thread" => $thread,
                                    "posts" => $posts,
                                    "post_count" => $post_count,
                                    "page" => $page,
                                    "reply_form" => $form->createView()
                                )
                            );
    }
    
    /**
     * @Route("/forum/{forum_slug}/{id}/lock", name="thread_lock")
     * @ParamConverter("forum", options={"mapping" = {"forum_slug": "slug"}})
     * @Entity("thread", expr="repository.findByCompactId(id)")
     */
    public function thread_lock(Request $request, Forum $forum, Thread $thread) {
        $em = $this->getDoctrine()->getManager();
        
        $this->denyAccessUnlessGranted('lock', $thread);

        $form = $this->createForm(LockType::class, $thread);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thread = $form->getData();
            
            $em->persist($thread);
            $em->flush();

            return $this->redirectToRoute("f9kforum_thread", array("forum_id" => $forum->getSlug(), "id" => $thread->getCompactId()));
        }

        return $this->render(
                                "forum/thread_lock.html.twig",
                                array(
                                    "forum" => $forum,
                                    "thread" => $thread,
                                    "lock_form" => $form->createView()
                                )
                            );
    }
}
