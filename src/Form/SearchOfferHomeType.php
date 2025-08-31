<?php

namespace App\Form;

use App\Entity\Skill;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchOfferHomeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('skillOffered', EntityType::class, [
                'class' => Skill::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => 'Compétence OFFERTE (optionnel)',
                'required' => false,
            ])
            ->add('skillRequested', EntityType::class, [
                'class' => Skill::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => 'Compétence DEMANDÉE (optionnel)',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // utilisé en GET → pas besoin de CSRF
            'csrf_protection' => false,
            // tolère des paramètres en plus dans l’URL
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * Génère des paramètres plats dans l’URL : ?skillOffered=...&skillRequested=...
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
