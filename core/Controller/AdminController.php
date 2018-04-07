<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\Criteria;

use Forum9000\Entity\User;
use Forum9000\Entity\Forum;
use Forum9000\Entity\Permission;
use Forum9000\Entity\Grant;
use Forum9000\Form\ForumType;
use Forum9000\Form\PermissionType;
use Forum9000\Form\GrantType;
use Forum9000\Form\UserType;

/** Backdoor access for ROLE_ADMIN users.
 *
 * NOTE: It is extremely important not to grant ROLE_ADMIN carelessly. The
 * difference between ROLE_USER and ROLE_ADMIN is that the latter is allowed to
 * make use of /admin views to modify the site regardless of what the
 * permissions and grants say. Essentially, ADMIN is intended for managing the
 * platform as a whole, while forum specific management should be handled
 * through Grants.
 * 
 * @Route("/admin", name="f9kadmin_")
 */
class AdminController extends Controller {
    /**
     * @Route("/users", name="user_overview")
     */
    public function user_overview(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $this->getDoctrine()->getRepository(User::class);

        $users = $userRepo->findAll();

        return $this->render(
            "admin/users.html.twig",
            array(
                "users" => $users
            )
        );
    }

    /**
     * @Route("/users/{id}", name="user_single")
     */
    public function user_single(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->findByCompactId($id);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("f9kadmin_user_single", array("id" => $user->getCompactId()));
        }

        return $this->render(
            "admin/user_single.html.twig",
            array(
                "user" => $user,
                "user_form" => $form->createView(),
            )
        );
    }

    /**
     * @Route("/forums", name="forum_overview")
     */
    public function forum_overview(Request $request) {
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
    
    /**
     * @Route("/forums/{id}", name="forum_single")
     */
    public function forum_single(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $forumRepo = $this->getDoctrine()->getRepository(Forum::class);
        $forum = $forumRepo->findByCompactId($id);
        
        $new_perm = new Permission();
        $new_perm->setForum($forum);
        $new_perm_form = $this->createForm(PermissionType::class, $new_perm, array(
            'action' => $this->generateUrl('f9kadmin_forum_perms', array("id" => $id))
        ));
        
        $new_grant = new Grant();
        $new_grant->setForum($forum);
        $new_grant_form = $this->createForm(GrantType::class, $new_grant, array(
            'action' => $this->generateUrl('f9kadmin_forum_grants', array("id" => $id))
        ));
        
        $grant_user_sort = Criteria::create()
            ->orderBy(array("user" => Criteria::ASC));
        
        $grants_by_user = $forum->getGrants()->matching($grant_user_sort);
        
        return $this->render(
            "admin/forum_single.html.twig",
            array(
                "forum" => $forum,
                "grants_by_user" => $grants_by_user,
                "new_perm_form" => $new_perm_form->createView(),
                "new_grant_form" => $new_grant_form->createView()
            )
        );
    }
    
    /**
     * @Route("/forums/{id}/perms", name="forum_perms")
     */
    public function forum_perms(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $forumRepo = $this->getDoctrine()->getRepository(Forum::class);
        $forum = $forumRepo->findByCompactId($id);
        
        //Recreate a form object to capture the request data.
        $perm = new Permission();
        $perm->setForum($forum);
        
        $perm_form = $this->createForm(PermissionType::class, $perm);
        $perm_form->handleRequest($request);
        
        if ($perm_form->isSubmitted() && $perm_form->isValid()) {
            $perm = $perm_form->getData();
            
            $em->merge($perm);
            $em->flush();
        }
        
        return $this->redirectToRoute("f9kadmin_forum_single", array("id" => $id));
    }
    
    /**
     * @Route("/forums/{id}/grants", name="forum_grants")
     */
    public function forum_grants(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $forumRepo = $this->getDoctrine()->getRepository(Forum::class);
        $forum = $forumRepo->findByCompactId($id);
        
        //Recreate a form object to capture the request data.
        $grant = new Grant();
        $grant->setForum($forum);
        $grant->setGrantStatus(null);
        
        $grant_form = $this->createForm(GrantType::class, $grant);
        $grant_form->handleRequest($request);
        
        if ($grant_form->isSubmitted() && $grant_form->isValid()) {
            $grant = $grant_form->getData();
            
            $em->merge($grant);
            $em->flush();
        }
        
        return $this->redirectToRoute("f9kadmin_forum_single", array("id" => $id));
    }
}