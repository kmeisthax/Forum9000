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
        
        if ($user !== null) {
            //First, check if the user has an explicit Grant record.
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq("user", $user))
                ->where(Criteria::expr()->eq("attribute", $attribute));

            foreach ($subject->getGrants()->matching($criteria) as $grant) {
                if ($grant->getIsGranted()) return true;
                else if ($grant->getIsDenied()) return false;
            }
        }
        
        //Check if there's a default permission
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("attribute", $attribute));
        
        foreach ($subject->getPermissions()->matching($criteria) as $perm) {
            if ($user !== null) return $perm->getIsGrantedAuth();
            else return $perm->getIsGrantedAnon();
        }
        
        //Neither a default permission nor a user grant exists, so access
        //is denied.
        return false;
    }
}
