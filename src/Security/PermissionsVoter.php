<?php

namespace App\Security;

use App\Entity\Forum;
use App\Entity\User;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Doctrine\Common\Collections\Criteria;

class PermissionsVoter extends Voter {
    protected function supports($attribute, $subject) {
        if (!in_array($attribute, array(Permission::VIEW, Permission::POST, Permission::REPLY, Permission::GRANT, Permission::REVOKE))) {
            return false;
        }
        
        if (!$subject instanceof Forum) {
            return false;
        }
        
        return true;
    }
    
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        $user = $token->getUser();
        
        //First, check if the user has an explicit permissions grant.
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("user", $user))
            ->where(Criteria::expr()->eq("attribute", $attribute));
        
        foreach ($subject->getPermissions->matching($criteria) as $perm) {
            return true;
        }
        
        //Finally, check if there's a blanket permission grant to all users.
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("user", null))
            ->where(Criteria::expr()->eq("attribute", $attribute));
        
        foreach ($subject->getPermissions->matching($criteria) as $perm) {
            return true;
        }
        
        //If no permission grants us this action then we must vote to deny
        return false;
    }
}