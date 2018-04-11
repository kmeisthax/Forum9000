<?php

namespace Forum9000\Security;

use Forum9000\Entity\Forum;
use Forum9000\Entity\Thread;
use Forum9000\Entity\User;
use Forum9000\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Doctrine\Common\Collections\Criteria;

class PermissionsVoter extends Voter {
    private $authtrust;
    
    public function __construct(AuthenticationTrustResolverInterface $authtrust) {
        $this->authtrust = $authtrust;
    }
    
    protected function supports($attribute, $subject) {
        if (!in_array($attribute, array(Permission::VIEW, Permission::POST, Permission::REPLY, Permission::LOCK, Permission::GRANT, Permission::REVOKE))) {
            return false;
        }
        
        if (!($subject instanceof Forum || $subject instanceof Thread)) {
            return false;
        }
        
        return true;
    }
    
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        $user = $token->getUser();
        
        if ($subject instanceof Thread) {
            //Threads don't have permissions or grants, they inherit from the
            //forum they belong to
            $subject = $subject->getForum();
        }
        
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
            if ($this->authtrust->isAnonymous($token)) return $perm->getIsGrantedAnon();
            else return $perm->getIsGrantedAuth();
        }
        
        //Neither a default permission nor a user grant exists, so access
        //is denied.
        return false;
    }
}
