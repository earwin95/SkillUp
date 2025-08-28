<?php

namespace App\DataFixtures;

use App\Entity\ExchangeRequest;
use App\Entity\Offer;
use App\Entity\User;
use App\Enum\ExchangeRequestStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ExchangeRequestFixtures extends Fixture implements DependentFixtureInterface
{
    public const COUNT = 40; // nombre de demandes à générer

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        //  Lecture directe en BDD
        /** @var User[] $users */
        $users  = $manager->getRepository(User::class)->findAll();
        /** @var Offer[] $offers */
        $offers = $manager->getRepository(Offer::class)->findAll();

        if (count($users) === 0 || count($offers) === 0) {
            return; // rien à faire si prérequis absents
        }

        // Empêcher plusieurs PENDING pour un même couple (requester, offer)
        $pendingPairs = [];

        $created = 0;
        $attempts = 0;
        $maxAttempts = 400;

        while ($created < self::COUNT && $attempts < $maxAttempts) {
            $attempts++;

            $requester = $faker->randomElement($users);
            $offer     = $faker->randomElement($offers);

            // pas de demande sur sa propre offre
            if ($offer->getOwner() && $offer->getOwner()->getId() === $requester->getId()) {
                continue;
            }

            // distribution réaliste des statuts
            $status = $faker->randomElement([
                ExchangeRequestStatus::PENDING,
                ExchangeRequestStatus::PENDING,
                ExchangeRequestStatus::ACCEPTED,
                ExchangeRequestStatus::ACCEPTED,
                ExchangeRequestStatus::DECLINED,
                ExchangeRequestStatus::CANCELLED,
            ]);

            // unicité sur PENDING pour (requester, offer)
            if ($status === ExchangeRequestStatus::PENDING) {
                $key = $requester->getId() . '-' . $offer->getId();
                if (isset($pendingPairs[$key])) {
                    continue;
                }
                $pendingPairs[$key] = true;
            }

            $er = (new ExchangeRequest())
                ->setRequester($requester)
                ->setOffer($offer)
                ->setStatus($status)
                ->setMessage($faker->optional(0.6)->sentence(12));

            // timestamps réalistes
            $createdAt = $faker->dateTimeBetween('-60 days', 'now');
            $updatedAt = (clone $createdAt);
            if ($status !== ExchangeRequestStatus::PENDING) {
                $updatedAt->modify('+' . $faker->numberBetween(0, 15) . ' days');
            }

            $er->setCreatedAt(\DateTimeImmutable::createFromMutable($createdAt));
            $er->setUpdatedAt(\DateTimeImmutable::createFromMutable($updatedAt));

            $manager->persist($er);
            $created++;
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        // On dépend de la présence des Users et Offers en base (fixtures déjà chargées)
        return [
            UserFixtures::class,
            OfferFixtures::class,
        ];
    }
}
