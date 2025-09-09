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
    public const NB_OFFERS = 30;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $created = 0;
        $seenCombos = []; // évite doublons 

        // utilitaires pour titres/descriptions crédibles
        $titlePatterns = [
            'Cours de %offered% (niveau débutant)',
            'Aide en %offered% contre %requested%',
            'Je propose %offered% • échange contre %requested%',
            'Session %offered% (2h) — recherche %requested%',
            '%offered% : coaching rapide, je cherche %requested%',
        ];

        $descTemplates = [
            "Bonjour ! Je propose une initiation à %offered%. On voit les bases, des exemples concrets et de la pratique. En échange, je cherche quelqu'un pour m'aider en %requested%.",
            "Je peux vous accompagner en %offered% (outils, bonnes pratiques, petits exercices). Contrepartie souhaitée : un coup de main en %requested%.",
            "Atelier %offered% de 1 à 2 heures, adapté à votre niveau. En retour, j'aimerais progresser sur %requested%.",
            "Partage d'expérience en %offered% (méthode simple et bienveillante). Je suis preneur de conseils/mentorat en %requested%.",
            "Séance %offered% (distanciel possible). De mon côté, j'aimerais apprendre %requested%.",
        ];

        // on veut exactement NB_OFFERS
        while ($created < self::NB_OFFERS) {
            /** @var \App\Entity\User $owner */
            $owner = $this->getReference(
                UserFixtures::REF_PREFIX . $faker->numberBetween(1, UserFixtures::NB_USERS),
                \App\Entity\User::class
            );

            // tire deux compétences différentes
            $offeredIdx = $faker->numberBetween(0, count(SkillFixtures::DEFAULT_SKILLS) - 1);
            do {
                $requestedIdx = $faker->numberBetween(0, count(SkillFixtures::DEFAULT_SKILLS) - 1);
            } while ($requestedIdx === $offeredIdx);

            /** @var \App\Entity\Skill $offered */
            $offered = $this->getReference(
                SkillFixtures::REF_PREFIX . $offeredIdx,
                \App\Entity\Skill::class
            );

            /** @var \App\Entity\Skill $requested */
            $requested = $this->getReference(
                SkillFixtures::REF_PREFIX . $requestedIdx,
                \App\Entity\Skill::class
            );

            // clé anti-doublon (même owner + même couple offered/requested)
            $key = sprintf('%d|%d|%d', $owner->getId(), $offered->getId(), $requested->getId());
            if (isset($seenCombos[$key])) {
                // déjà vu → on retente sans incrémenter
                continue;
            }
            $seenCombos[$key] = true;

            // fabrique un titre et une description réalistes
            $offeredName   = method_exists($offered, 'getName') ? $offered->getName() : 'Compétence A';
            $requestedName = method_exists($requested, 'getName') ? $requested->getName() : 'Compétence B';

            $title = str_replace(
                ['%offered%', '%requested%'],
                [$offeredName, $requestedName],
                $faker->randomElement($titlePatterns)
            );

            $description = str_replace(
                ['%offered%', '%requested%'],
                [$offeredName, $requestedName],
                $faker->randomElement($descTemplates)
            );

            $offer = new Offer();
            $offer->setTitle($title);
            $offer->setDescription($description);
            $offer->setStatus('active'); // adapte si tu utilises un enum

            // relations
            $offer->setOwner($owner);
            $offer->setSkillOffered($offered);
            $offer->setSkillRequested($requested);

            // si l'entité a un createdAt, on le renseigne de manière crédible
            if (method_exists($offer, 'setCreatedAt')) {
                $offer->setCreatedAt($faker->dateTimeBetween('-3 months', 'now'));
            }

            $manager->persist($offer);
            $created++;
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
