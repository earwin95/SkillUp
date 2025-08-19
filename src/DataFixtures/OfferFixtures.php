<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Offer;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\SkillFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OfferFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 30; $i++) {
            /** @var \App\Entity\User $owner */
            $owner = $this->getReference(
                UserFixtures::REF_PREFIX . $faker->numberBetween(1, UserFixtures::NB_USERS),
                \App\Entity\User::class
            );

            /** @var \App\Entity\Skill $offered */
            $offered = $this->getReference(
                SkillFixtures::REF_PREFIX . $faker->numberBetween(0, count(SkillFixtures::DEFAULT_SKILLS) - 1),
                \App\Entity\Skill::class
            );

            /** @var \App\Entity\Skill $requested */
            $requested = $this->getReference(
                SkillFixtures::REF_PREFIX . $faker->numberBetween(0, count(SkillFixtures::DEFAULT_SKILLS) - 1),
                \App\Entity\Skill::class
            );

            // Empêche d'avoir la même compétence demandée/offerte
            if ($offered === $requested) {
                continue;
            }

            $offer = new Offer();
            $offer->setTitle($faker->sentence(3));
            $offer->setDescription($faker->paragraph());
            $offer->setStatus('active');
            $offer->setOwner($owner);
            $offer->setSkillOffered($offered);
            $offer->setSkillRequested($requested);

            $manager->persist($offer);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['dev'];
    }

public function getDependencies(): array

    {
        return [
            UserFixtures::class,
            SkillFixtures::class,
        ];
    }
}
