<?php

namespace Forum9000\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Forum9000\Entity\User;

class UserType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        //TODO: Must restrict passwords < 4096 characters
        //TODO: Reconcile with RegistrationType
        //TODO: Do not allow selecting role outside of /admin
        $builder
            ->add("email", TextType::class)
            ->add("handle", TextType::class)
            ->add("siteRole", ChoiceType::class, array(
                'choices' => array(
                    User::USER => User::USER,
                    User::STAFF => User::STAFF,
                    User::DEVELOPER => User::DEVELOPER,
            )))
            ->add("register", SubmitType::class, array("label" => "Save"));
    }
}
