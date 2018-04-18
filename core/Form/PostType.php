<?php

namespace Forum9000\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Forum9000\MarkupLanguage\MarkupLanguageManager;

class PostType extends AbstractType {
    public $markupManager;

    public function __construct(MarkupLanguageManager $markupManager) {
        $this->markupManager = $markupManager;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'languages' => $this->markupManager->getMarkupLanguageChoices(),
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add("title", TextType::class)
            ->add("message", TextareaType::class)
            ->add("markupLanguage", ChoiceType::class, array("choices" => $options["languages"]))
            ->add("post", SubmitType::class, array("label" => "Reply"));
    }
}
