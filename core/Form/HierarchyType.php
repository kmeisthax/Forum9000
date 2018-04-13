<?php

namespace Forum9000\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class HierarchyType extends AbstractType {
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'entity_class' => null,
            'parent_label' => null
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add("parent", EntityType::class, array(
                'class' => $options["entity_class"],
                'choice_label' => $options["parent_label"]
            ))
            ->add("order", TextareaType::class);
    }
}
