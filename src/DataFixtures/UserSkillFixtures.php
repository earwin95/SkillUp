<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Skill;
use App\Entity\UserSkill;
use App\Enum\SkillLevel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserSkillFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        // 🔎 On va chercher directement en base (aucune référence utilisée)
        /** @var Skill[] $skills */
        $skills = $manager->getRepository(Skill::class)->findAll();
        /** @var User[] $users */
        $users  = $manager->getRepository(User::class)->findAll();

        if (!$skills || !$users) {
            // Rien à faire si l'un des deux est vide
            return;
        }

        foreach ($users as $user) {
            // 1 à 5 compétences aléatoires pour chaque user
            $howMany = $faker->numberBetween(1, min(5, count($skills)));
            // randomElements(array $array, int $count)
            $picked  = $faker->randomElements($skills, $howMany);

            foreach ($picked as $skill) {
                $us = new UserSkill();
                $us->setUser($user);
                $us->setSkill($skill);
                $us->setLevel($faker->randomElement([
                    SkillLevel::BEGINNER,
                    SkillLevel::INTERMEDIATE,
                    SkillLevel::ADVANCED,
                    SkillLevel::EXPERT,
                ]));
                $us->setNotes($faker->optional(0.5)->sentence(12));

                $manager->persist($us);
            }
        }

        $manager->flush();
    }

    // On garde les dépendances pour assurer l'ordre de chargement
    public function getDependencies(): array
    {
        return [
            SkillFixtures::class,
            UserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['dev'];
    }
}
