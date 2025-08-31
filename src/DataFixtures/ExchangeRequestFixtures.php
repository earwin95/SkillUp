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

        /** @var User[] $users */
        $users  = $manager->getRepository(User::class)->findAll();
        /** @var Offer[] $offers */
        $offers = $manager->getRepository(Offer::class)->findAll();

        if (empty($users) || empty($offers)) {
            return;
        }

        // messages plus réalistes
        $messages = [
            "Salut ! Ton offre m'intéresse, je débute et j’aimerais apprendre.",
            "Disponible le soir ou le week-end, motivé pour un échange.",
            "Je peux proposer un créneau cette semaine, intéressé par ton annonce.",
            "Ton offre correspond bien à ce que je cherche, partant pour un échange.",
            "Je débute, j'aimerais progresser avec ton aide.",
            "Est-ce que tu serais dispo pour un premier échange rapide ?",
            "Ton profil me paraît parfait, j’aimerais en discuter.",
            "Je cherche justement à échanger sur ce sujet, intéressé !",
        ];

        $pendingPairs = []; // pour interdire plusieurs PENDING identiques
        $seenCombos   = []; // anti-doublon global

        $created = 0;
        $attempts = 0;
        $maxAttempts = 500;

        while ($created < self::COUNT && $attempts < $maxAttempts) {
            $attempts++;

            $requester = $faker->randomElement($users);
            $offer     = $faker->randomElement($offers);

            // pas de demande sur sa propre offre
            if ($offer->getOwner()?->getId() === $requester->getId()) {
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

            // unicité PENDING pour (requester, offer)
            if ($status === ExchangeRequestStatus::PENDING) {
                $key = $requester->getId() . '-' . $offer->getId();
                if (isset($pendingPairs[$key])) {
                    continue;
                }
                $pendingPairs[$key] = true;
            }

            // clé anti-doublon globale
            $comboKey = $requester->getId() . '|' . $offer->getId() . '|' . $status->value;
            if (isset($seenCombos[$comboKey])) {
                continue;
            }
            $seenCombos[$comboKey] = true;

            // création
            $er = (new ExchangeRequest())
                ->setRequester($requester)
                ->setOffer($offer)
                ->setStatus($status)
                ->setMessage($faker->boolean(70) ? $faker->randomElement($messages) : null);

            // dates
            $createdAt = $faker->dateTimeBetween('-60 days', 'now');
            $updatedAt = clone $createdAt;
            if ($status !== ExchangeRequestStatus::PENDING) {
                $updatedAt->modify('+' . $faker->numberBetween(1, 15) . ' days');
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
        return [
            UserFixtures::class,
            OfferFixtures::class,
        ];
    }
}
