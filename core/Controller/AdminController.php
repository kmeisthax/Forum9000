<?php

namespace Forum9000\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\Criteria;

use Forum9000\Entity\User;
use Forum9000\Entity\Group;
use Forum9000\Entity\Forum;
use Forum9000\Entity\Post;
use Forum9000\Entity\Permission;
use Forum9000\Entity\Grant;
use Forum9000\Entity\Membership;
use Forum9000\Form\ForumType;
use Forum9000\Form\ForumOrderingType;
use Forum9000\Form\PermissionType;
use Forum9000\Form\GrantType;
use Forum9000\Form\UserType;
use Forum9000\Form\GroupType;
use Forum9000\Form\MembershipType;
use Forum9000\Theme\Annotation\Theme;

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
 * @Theme(routeClass="admin")
 */
class AdminController extends Controller {
    /**
     * @Route("/", name="dashboard")
     */
    function homepage(Request $req) {
        $em = $this->getDoctrine()->getManager();
        $forumRepo = $this->getDoctrine()->getRepository(Forum::class);
        $postRepo = $this->getDoctrine()->getRepository(Post::class);
        
        $forums = $forumRepo->findAllRootForums();
        $newest_posts = $postRepo->getLatestPosts(0, 10);
        
        return $this->render(
            'admin/dashboard.html.twig',
            array(
                "forums" => $forums,
                "newest_posts" => $newest_posts
            )
        );
    }
    
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
     * @Entity("user", expr="repository.findByCompactId(id)")
     */
    public function user_single(Request $request, User $user) {
        $em = $this->getDoctrine()->getManager();
        
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
        $forum->setOrder(0);

        $new_forum_form = $this->createForm(ForumType::class, $forum, array(
            'action' => $this->generateUrl('f9kadmin_forum_create')
        ));

        $forums = $forumRepo->findAll();
        $order_form = $this->createForm(ForumOrderingType::class, array('forums' => $forums));
        $order_form->handleRequest($request);
        
        if ($order_form->isSubmitted() && $order_form->isValid()) {
            $form_data = $order_form->getData();
            
            foreach ($form_data["forums"] as $forum) {
                $em->persist($forum);
            }
            
            $em->flush();
            
            return $this->redirectToRoute("f9kadmin_forum_overview");
        }

        return $this->render(
                                "admin/forums.html.twig",
                                array(
                                    "forums" => $forums,
                                    "order_form" => $order_form->createView(),
                                    "new_forum_form" => $new_forum_form->createView()
                                )
                            );
    }

    /**
     * @Route("/forums/create", name="forum_create")
     */
    public function forum_create(Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        $forum = new Forum();
        $forum->setOrder(0);
        
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $forum = $form->getData();

            $em->persist($forum);
            $em->flush();
        
            return $this->redirectToRoute("f9kadmin_forum_single", array("id" => $forum->getCompactId()));
        }
        
        return $this->redirectToRoute("f9kadmin_forum_overview");
    }
    
    /**
     * @Route("/forums/{id}", name="forum_single")
     * @Entity("forum", expr="repository.findByCompactId(id)")
     */
    public function forum_single(Request $request, Forum $forum) {
        $em = $this->getDoctrine()->getManager();
        
        $forum_edit_form = $this->createForm(ForumType::class, $forum);
        $forum_edit_form->handleRequest($request);
        
        if ($forum_edit_form->isSubmitted() && $forum_edit_form->isValid()) {
            $forum = $forum_edit_form->getData();
            
            $em->merge($forum);
            $em->flush();
        }

        $new_perm = new Permission();
        $new_perm->setEstate($forum->getEstate());
        $new_perm_form = $this->createForm(PermissionType::class, $new_perm, array(
            'action' => $this->generateUrl('f9kadmin_forum_perms', array("id" => $forum->getCompactId()))
        ));
        
        $new_grant = new Grant();
        $new_grant->setEstate($forum->getEstate());
        $new_grant_form = $this->createForm(GrantType::class, $new_grant, array(
            'action' => $this->generateUrl('f9kadmin_forum_grants', array("id" => $forum->getCompactId()))
        ));
        
        $grant_user_sort = Criteria::create()
            ->orderBy(array("actor" => Criteria::ASC));
        
        $grants_by_user = $forum->getGrants()->matching($grant_user_sort);
        
        return $this->render(
            "admin/forum_single.html.twig",
            array(
                "forum" => $forum,
                "grants_by_user" => $grants_by_user,
                "forum_edit_form" => $forum_edit_form->createView(),
                "new_perm_form" => $new_perm_form->createView(),
                "new_grant_form" => $new_grant_form->createView()
            )
        );
    }
    
    /**
     * @Route("/forums/{id}/perms", name="forum_perms")
     * @Entity("forum", expr="repository.findByCompactId(id)")
     */
    public function forum_perms(Request $request, Forum $forum) {
        $em = $this->getDoctrine()->getManager();
        
        //Recreate a form object to capture the request data.
        $perm = new Permission();
        $perm->setEstate($forum->getEstate());
        $perm->setIsDeniedAnon(false);
        $perm->setIsDeniedAuth(false);
        
        $perm_form = $this->createForm(PermissionType::class, $perm);
        $perm_form->handleRequest($request);
        
        if ($perm_form->isSubmitted() && $perm_form->isValid()) {
            $perm = $perm_form->getData();
            
            $em->merge($perm);
            $em->flush();
        }
        
        return $this->redirectToRoute("f9kadmin_forum_single", array("id" => $forum->getCompactId()));
    }
    
    /**
     * @Route("/forums/{id}/grants", name="forum_grants")
     * @Entity("forum", expr="repository.findByCompactId(id)")
     */
    public function forum_grants(Request $request, Forum $forum) {
        $em = $this->getDoctrine()->getManager();
        
        //Recreate a form object to capture the request data.
        $grant = new Grant();
        $grant->setEstate($forum->getEstate());
        $grant->setGrantStatus(null);
        
        $grant_form = $this->createForm(GrantType::class, $grant);
        $grant_form->handleRequest($request);
        
        if ($grant_form->isSubmitted() && $grant_form->isValid()) {
            $grant = $grant_form->getData();
            
            $em->merge($grant);
            $em->flush();
        }
        
        return $this->redirectToRoute("f9kadmin_forum_single", array("id" => $forum->getCompactId()));
    }

    /**
     * @Route("/groups", name="group_overview")
     */
    public function group_overview(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $groupRepo = $this->getDoctrine()->getRepository(Group::class);
        
        $group = new Group();
        $new_group_form = $this->createForm(GroupType::class, $group, array(
            'action' => $this->generateUrl('f9kadmin_group_create')
        ));

        $groups = $groupRepo->findAll();

        return $this->render(
            "admin/groups.html.twig",
            array(
                "groups" => $groups,
                "new_group_form" => $new_group_form->createView()
            )
        );
    }

    /**
     * @Route("/groups/create", name="group_create")
     */
    public function group_create(Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $group = $form->getData();

            $em->persist($group);
            $em->flush();

            return $this->redirectToRoute("f9kadmin_group_single", array("id" => $group->getCompactId()));
        }

        return $this->redirectToRoute("f9kadmin_group_overview");
    }

    /**
     * @Route("/groups/{id}", name="group_single")
     * @Entity("group", expr="repository.findByCompactId(id)")
     */
    public function group_single(Request $request, Group $group) {
        $em = $this->getDoctrine()->getManager();
        
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $group = $form->getData();

            $em->persist($group);
            $em->flush();

            return $this->redirectToRoute("f9kadmin_group_single", array("id" => $group->getCompactId()));
        }

        $new_perm = new Permission();
        $new_perm->setEstate($group->getEstate());
        $new_perm_form = $this->createForm(PermissionType::class, $new_perm, array(
            'action' => $this->generateUrl('f9kadmin_group_perms', array("id" => $group->getCompactId()))
        ));

        $new_grant = new Grant();
        $new_grant->setEstate($group->getEstate());
        $new_grant_form = $this->createForm(GrantType::class, $new_grant, array(
            'action' => $this->generateUrl('f9kadmin_group_grants', array("id" => $group->getCompactId()))
        ));

        $new_member = new Membership();
        $new_member->setGroup($group);
        $new_member_form = $this->createForm(MembershipType::class, $new_member, array(
            'action' => $this->generateUrl('f9kadmin_group_memberships', array("id" => $group->getCompactId()))
        ));

        $grant_user_sort = Criteria::create()
            ->orderBy(array("actor" => Criteria::ASC));

        $grants_by_user = $group->getGrants()->matching($grant_user_sort);

        return $this->render(
            "admin/group_single.html.twig",
            array(
                "group" => $group,
                "grants_by_user" => $grants_by_user,
                "group_edit_form" => $form->createView(),
                "new_perm_form" => $new_perm_form->createView(),
                "new_grant_form" => $new_grant_form->createView(),
                "new_member_form" => $new_member_form->createView(),
            )
        );
    }

    /**
     * @Route("/groups/{id}/perms", name="group_perms")
     * @Entity("group", expr="repository.findByCompactId(id)")
     */
    public function group_perms(Request $request, Group $group) {
        $em = $this->getDoctrine()->getManager();
        
        //Recreate a form object to capture the request data.
        $perm = new Permission();
        $perm->setEstate($group->getEstate());
        $perm->setIsDeniedAnon(false);
        $perm->setIsDeniedAuth(false);

        $perm_form = $this->createForm(PermissionType::class, $perm);
        $perm_form->handleRequest($request);

        if ($perm_form->isSubmitted() && $perm_form->isValid()) {
            $perm = $perm_form->getData();

            $em->merge($perm);
            $em->flush();
        }

        return $this->redirectToRoute("f9kadmin_group_single", array("id" => $group->getCompactId()));
    }

    /**
     * @Route("/groups/{id}/grants", name="group_grants")
     * @Entity("group", expr="repository.findByCompactId(id)")
     */
    public function group_grants(Request $request, Group $group) {
        $em = $this->getDoctrine()->getManager();

        //Recreate a form object to capture the request data.
        $grant = new Grant();
        $grant->setEstate($group->getEstate());
        $grant->setGrantStatus(null);

        $grant_form = $this->createForm(GrantType::class, $grant);
        $grant_form->handleRequest($request);

        if ($grant_form->isSubmitted() && $grant_form->isValid()) {
            $grant = $grant_form->getData();

            $em->merge($grant);
            $em->flush();
        }

        return $this->redirectToRoute("f9kadmin_group_single", array("id" => $group->getCompactId()));
    }

    /**
     * @Route("/groups/{id}/memberships", name="group_memberships")
     * @Entity("group", expr="repository.findByCompactId(id)")
     */
    public function group_memberships(Request $request, Group $group) {
        $em = $this->getDoctrine()->getManager();
        
        //Recreate a form object to capture the request data.
        $new_member = new Membership();
        $new_member->setGroup($group);
        $new_member_form = $this->createForm(MembershipType::class, $new_member);
        $new_member_form->handleRequest($request);

        if ($new_member_form->isSubmitted() && $new_member_form->isValid()) {
            $new_member = $new_member_form->getData();

            $group->getMemberships()->add($new_member);

            $em->persist($new_member);
            $em->persist($group);
            $em->flush();
        }

        return $this->redirectToRoute("f9kadmin_group_single", array("id" => $group->getCompactId()));
    }
}
