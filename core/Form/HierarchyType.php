<?php

namespace Forum9000\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class HierarchyType extends AbstractType {
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'parent_class' => null,
            'parent_label' => null
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add("parent", EntityType::class, array(
                'class' => $options["parent_class"],
                'choice_label' => $options["parent_label"],
                'required' => false,
                'placeholder' => ' - No parent forum - '
            ))
            ->add("order", NumberType::class);
    }
}
