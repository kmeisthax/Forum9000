<?php

namespace Forum9000\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionsType extends AbstractType {
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'actions' => array()
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        foreach ($options["actions"] as $key => $label) {
            $builder->add($key, SubmitType::class, array("label" => $label));
        }
    }
}
