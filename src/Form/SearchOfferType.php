<?php

namespace App\Form;

use App\Entity\Skill;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchOfferType extends AbstractType
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
            ])
            ->add('q', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Mot-clé (titre, description ou nom de compétence)…',
                    'autocomplete' => 'off',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
