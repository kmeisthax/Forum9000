<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Criteria;

use App\Entity\Forum;
use App\Form\ForumType;

/* Backdoor access for ROLE_ADMIN users.
 *
 * NOTE: It is extremely important not to grant ROLE_ADMIN carelessly. The
 * difference between ROLE_USER and ROLE_ADMIN is that the latter is allowed to
 * make use of /admin views to modify the site regardless of what the
 * permissions and grants say. Essentially, ADMIN is intended for managing the
 * platform as a whole, while forum specific management should be handled
 * through Grants.
 */
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
    
    /**
     * @Route("/admin/forums/{id}")
     */
    public function forum_single(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $forumRepo = $this->getDoctrine()->getRepository(Forum::class);
        $forum = $forumRepo->findByCompactId($id);
        
        return $this->render(
            "admin/forum_single.html.twig",
            array(
                "forum" => $forum
            )
        )
    }
}
