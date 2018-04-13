<?php

namespace Forum9000\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Forum9000\Entity\Forum;

class ForumOrderingType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add("forums", CollectionType::class, array(
                'entry_type' => HierarchyType::class,
                'entry_options' => array(
                    "entity_class" => Forum::class,
                    "parent_label" => "title"
                )
            ))
            ->add("save", SubmitType::class, array("label" => "Save Ordering"));
    }
}
