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
            // Sélecteur de Skill (liste des compétences existantes)
            ->add('skill', EntityType::class, [
                'class' => Skill::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une compétence…',
            ])
            // Enum -> select
            ->add('level', ChoiceType::class, [
                // labels => valeurs (enum cases)
                'choices' => [
                    'Beginner'     => SkillLevel::BEGINNER,
                    'Intermediate' => SkillLevel::INTERMEDIATE,
                    'Advanced'     => SkillLevel::ADVANCED,
                    'Expert'       => SkillLevel::EXPERT,
                ],
                'expanded' => false, // select <select>
                'multiple' => false,
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSkill::class,
        ]);
    }
}
