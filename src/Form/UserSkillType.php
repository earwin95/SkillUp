<?php

namespace App\Form;

use App\Entity\UserSkill;
use App\Entity\Skill;
use App\Enum\SkillLevel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('skill', EntityType::class, [
                'class' => Skill::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une compétence…',
                'disabled' => $options['disable_skill'], // ← on peut le désactiver en édition
            ])
            ->add('level', ChoiceType::class, [
                'choices' => [
                    'Beginner'     => SkillLevel::BEGINNER,
                    'Intermediate' => SkillLevel::INTERMEDIATE,
                    'Advanced'     => SkillLevel::ADVANCED,
                    'Expert'       => SkillLevel::EXPERT,
                ],
                'placeholder' => 'Sélectionner un niveau…',
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Notes (optionnel)…',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => UserSkill::class,
            'disable_skill' => false, // par défaut on peut choisir la skill
        ]);

        $resolver->setAllowedTypes('disable_skill', 'bool');
    }
}
