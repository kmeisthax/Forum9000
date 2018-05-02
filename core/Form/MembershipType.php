<?php

namespace Forum9000\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Forum9000\Entity\Forum;
use Forum9000\Entity\Actor;
use Forum9000\Entity\Group;
use Forum9000\Entity\Permission;

class MembershipType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        if ($options["data"]->getGroup() === null) {
            $builder->add("group", EntityType::class, array(
                'class' => Group::class,
                'choice_label' => 'handle'
            ));
        }

        $builder->add("member", EntityType::class, array(
            'class' => Actor::class,
            'choice_label' => 'handle'
        ));

        $builder
            ->add("set", SubmitType::class, array("label" => "Save"));
    }
}
