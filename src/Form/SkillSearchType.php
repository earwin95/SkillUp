<?php
// FORM DU HOME (BARRE DE RECHERCHE DES COMPETENCES)
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('skill', TextType::class, [
            'required' => false,
            'attr' => [
                'placeholder' => 'Rechercher une compétence (ex: guitare, photoshop, anglais)…',
                'autocomplete' => 'off',
            ],
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
