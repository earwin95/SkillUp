<?php

namespace App\DataFixtures;

use App\Entity\Skill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class SkillFixtures extends Fixture implements FixtureGroupInterface
{
    public const DEFAULT_SKILLS = [
        'Autre (général)',
        'Développement Web',
        'Développement Mobile',
        'Design UI/UX',
        'Langues',
        'Marketing Digital',
        'Bricolage',
        'Cuisine',
        'Musique',
        'Photographie',
    ];

    public const REF_PREFIX = 'skill_';

    public function load(ObjectManager $manager): void
    {
        foreach (self::DEFAULT_SKILLS as $i => $name) {
            $skill = (new Skill())
                ->setName($name);
            $manager->persist($skill);

            // On stocke une référence pour les autres fixtures
            $this->addReference(self::REF_PREFIX.$i, $skill);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        // Chargées seulement en env "dev"
        return ['dev'];
    }
}
