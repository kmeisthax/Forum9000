<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Forum;
use App\Entity\User;
use App\Entity\Permission;

class GrantType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        if ($options["data"]->getForum() === null) {
            $builder->add("forum", EntityType::class, array(
                'class' => Forum::class,
                'choice_label' => 'title'
            ));
        }
        
        if ($options["data"]->getAttribute() === null) {
            $builder->add("attribute", ChoiceType::class, array(
                'choices' => array(
                    Permission::VIEW => Permission::VIEW,
                    Permission::POST => Permission::POST,
                    Permission::REPLY => Permission::REPLY,
                    Permission::GRANT => Permission::GRANT,
                    Permission::REVOKE => Permission::REVOKE)
            ));
        }
        
        if ($options["data"]->getUser() === null) {
            $builder->add("user", EntityType::class, array(
                'class' => User::class,
                'choice_label' => 'handle'
            ));
        }
        
        $builder
            ->add("grantStatus", ChoiceType::class, array(
                'choices' => array(
                    'Granted' => true,
                    'Undetermined' => null,
                    'Denied' => false,
                )
            ))
            ->add("set", SubmitType::class, array("label" => "Set"));
    }
}
