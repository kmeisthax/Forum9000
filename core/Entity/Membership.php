<?php

namespace Forum9000\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Entity()
 */
class Membership {
    use \Forum9000\Timestamps\TimestampedEntityTrait;
    
    /**
     * @ORM\Id();
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="memberships")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    public $group;
    
    /**
     * The user or group that is a member of the given group.
     * 
     * Creating a membership of one group in another group allows membership to
     * be transitive. Let's say you have a group, and you also want to
     * distinguish owners from other members. Adding owners collectively as a
     * group means that they will also be treated as members. It also means that
     * they will share the same joining date, so that regular members with evict
     * granted to them cannot evict owners.
     * 
     * @ORM\Id();
     * @ORM\ManyToOne(targetEntity="Actor", inversedBy="memberships")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id")
     */
    public $member;
    
    public function __construct($user, $group) {
        $this->ctime = new \DateTime();
    }
    
    public function getMember() : Actor {
        return $this->member;
    }
    
    public function setMember(Actor $member) {
        $this->member = $member;
    }
    
    public function getGroup() : Group {
        return $this->group;
    }
    
    public function setGroup(Group $group) {
        $this->group = $group;
    }
    
    /**
     * Calculate if a given user has seniority over this one.
     * 
     * Seniority is defined as this membership being older than the other user's
     * membership in the same group. If a user is a member of a group that is a
     * member of the group, then they will inherit the seniority of the group
     * they inherited the membership from.
     * 
     * If two actors joined at the same time then they both have seniority over
     * each other.
     * 
     * If the actor is not a member of the group then this membership is treated
     * as senior to it. In other words, all non-members are treated the same as
     * if they had joined up at the end of time.
     * 
     * Returns FALSE if and only if the other actor is a member and has been a
     * member for longer than this membership's member.
     */
    public function hasSeniorityOverMember(Actor $user) {
        $directMembershipCriteria = Criteria::create()
            ->where(Criteria::expr()->eq("member", $user));
        
        //TODO: How do we actually list all members that are groups!?
        //I'd rather not do it in-memory...
        
        foreach ($this->group->getMemberships()->matching($directMembershipCriteria) as $otherMem) {
            if ($this->ctime <= $otherMem->getCtime()) {
                return true;
            }
        }
        
        return false;
    }
}