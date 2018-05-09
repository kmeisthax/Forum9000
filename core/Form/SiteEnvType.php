<?php

namespace Forum9000\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SiteEnvType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        //TODO: TRUSTED_PROXIES, TRUSTED_HOSTS, MAILER_URL
        $builder->add("APP_ENV", ChoiceType::class, array(
            'label' => "Install type",
            'choices' => array(
                'Production' => 'prod',
                'Testing' => 'test',
                'Development' => 'dev',
            ),
            'expanded' => true
        ))->add("DATABASE_URL", TextType::class, array(
            'label' => "Database connection"
        ))->add("owner_registration", RegistrationType::class);
    }
}
