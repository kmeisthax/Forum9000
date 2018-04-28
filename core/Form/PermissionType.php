<?php

namespace Forum9000\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Forum9000\Entity\Forum;
use Forum9000\Entity\Permission;

class PermissionType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        if ($options["data"]->getAttribute() === null) {
            $builder->add("attribute", ChoiceType::class, array(
                'choices' => array(
                    Permission::VIEW => Permission::VIEW,
                    Permission::POST => Permission::POST,
                    Permission::REPLY => Permission::REPLY,
                    Permission::LOCK => Permission::LOCK,
                    Permission::GRANT => Permission::GRANT,
                    Permission::REVOKE => Permission::REVOKE)
            ));
        }
        
        $builder
            ->add("isGrantedAnon", ChoiceType::class, array(
                'choices' => array(
                    'Yes' => true,
                    'No' => false,
                )
            ))
            ->add("isGrantedAuth", ChoiceType::class, array(
                'choices' => array(
                    'Yes' => true,
                    'No' => false,
                )
            ))
            ->add("set", SubmitType::class, array("label" => "Set"));
    }
}
