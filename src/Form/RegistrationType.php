<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RegistrationType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        //TODO: Must restrict passwords < 4096 characters
        $builder
            ->add("email", TextType::class)
            ->add("password", PasswordType::class)
            ->add("handle", TextType::class)
            ->add("register", SubmitType::class, array("label" => "Register"));
    }
}
