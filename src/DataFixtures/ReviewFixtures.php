<?php

namespace App\DataFixtures;

use App\Entity\Review;
use App\Entity\ExchangeRequest;
use App\Entity\Offer;
use App\Entity\User;
use App\Enum\ExchangeRequestStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public const COUNT_EXTRA = 10; // reviews hors échanges

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Petites phrases réalistes (sans lorem)
        $posComments = [
            "Très pédagogue et à l’écoute, j’ai bien progressé.",
            "Explications claires, exemples concrets, super séance.",
            "Ponctuel et sympa, je recommande sans hésiter.",
            "Bonne préparation, on va droit à l’essentiel.",
            "Ambiance détendue et efficace, merci !",
            "Beaucoup de conseils utiles, j’ai appris plein de choses.",
            "Communication fluide, très satisfait de l’échange.",
            "Exemples adaptés à mon niveau, top !",
            "Patient et méthodique, super expérience.",
            "Je referai appel à lui/elle, merci encore.",
        ];

        $neutralComments = [
            "Séance correcte, quelques points à approfondir.",
            "Bon échange globalement, merci.",
            "Contenu utile, mais pourrait aller un peu plus loin.",
            "Rien à redire, c’était bien.",
            "Bon contact, je recommande.",
        ];

        // ✅ 1) Reviews liées aux échanges ACCEPTED
        /** @var ExchangeRequest[] $acceptedRequests */
        $acceptedRequests = $manager->getRepository(ExchangeRequest::class)
            ->findBy(['status' => ExchangeRequestStatus::ACCEPTED]); // OK si enum mappée

        // Anti-doublons sur (authorId|subjectId|offerId|exchangeId)
        $seen = [];

        foreach ($acceptedRequests as $er) {
            $requester = $er->getRequester();
            $offer     = $er->getOffer();
            $owner     = $offer?->getOwner();

            if (!$requester || !$owner || !$offer || $requester->getId() === $owner->getId()) {
                continue;
            }

            // Base temporelle : si createdAt inexistant, on prend -30 jours → maintenant
            $erCreated = method_exists($er, 'getCreatedAt') && $er->getCreatedAt()
                ? $er->getCreatedAt()
                : new \DateTimeImmutable('-30 days');

            // a) requester -> owner (note 4-5, 90% de chances d’un commentaire positif)
            $key1 = sprintf('%d|%d|%d|%d', $requester->getId(), $owner->getId(), $offer->getId(), $er->getId());
            if (!isset($seen[$key1])) {
                $seen[$key1] = true;

                $r1 = (new Review())
                    ->setAuthor($requester)
                    ->setSubjectUser($owner)
                    ->setOffer($offer)
                    ->setExchangeRequest($er)
                    ->setRating($faker->numberBetween(4, 5))
                    ->setComment($faker->boolean(90)
                        ? $faker->randomElement($posComments)
                        : $faker->randomElement($neutralComments)
                    );

                $createdAt1 = $faker->dateTimeBetween($erCreated->format('Y-m-d H:i:s'), 'now');
                $updatedAt1 = (clone $createdAt1)->modify('+' . $faker->numberBetween(0, 7) . ' days');

                $r1->setCreatedAt(\DateTimeImmutable::createFromMutable($createdAt1));
                $r1->setUpdatedAt(\DateTimeImmutable::createFromMutable($updatedAt1));

                $manager->persist($r1);
            }

            // b) owner -> requester (70% des cas), note 4-5
            if ($faker->boolean(70)) {
                $key2 = sprintf('%d|%d|%d|%d', $owner->getId(), $requester->getId(), $offer->getId(), $er->getId());
                if (!isset($seen[$key2])) {
                    $seen[$key2] = true;

                    $r2 = (new Review())
                        ->setAuthor($owner)
                        ->setSubjectUser($requester)
                        ->setOffer($offer)
                        ->setExchangeRequest($er)
                        ->setRating($faker->numberBetween(4, 5))
                        ->setComment($faker->boolean(85)
                            ? $faker->randomElement($posComments)
                            : $faker->randomElement($neutralComments)
                        );

                    $createdAt2 = $faker->dateTimeBetween($erCreated->format('Y-m-d H:i:s'), 'now');
                    $updatedAt2 = (clone $createdAt2)->modify('+' . $faker->numberBetween(0, 7) . ' days');

                    $r2->setCreatedAt(\DateTimeImmutable::createFromMutable($createdAt2));
                    $r2->setUpdatedAt(\DateTimeImmutable::createFromMutable($updatedAt2));

                    $manager->persist($r2);
                }
            }
        }

        // ✅ 2) Quelques reviews “libres” (pas forcément liées à un échange)
        /** @var User[] $users */
        $users  = $manager->getRepository(User::class)->findAll();
        /** @var Offer[] $offers */
        $offers = $manager->getRepository(Offer::class)->findAll();

        for ($i = 0; $i < self::COUNT_EXTRA; $i++) {
            if (count($users) < 2) {
                break;
            }
            $author  = $faker->randomElement($users);
            $subject = $faker->randomElement($users);
            if ($author->getId() === $subject->getId()) {
                $i--; // rejoue ce tour pour éviter self-review
                continue;
            }

            $review = (new Review())
                ->setAuthor($author)
                ->setSubjectUser($subject)
                // note 3-5 pour mimer une variabilité
                ->setRating($faker->numberBetween(3, 5))
                ->setComment($faker->boolean(75)
                    ? $faker->randomElement($posComments)
                    : $faker->randomElement($neutralComments)
                );

            // Optionnellement rattacher une offre
            if (!empty($offers) && $faker->boolean(60)) {
                $review->setOffer($faker->randomElement($offers));
            }

            $c = $faker->dateTimeBetween('-45 days', 'now');
            $u = (clone $c)->modify('+' . $faker->numberBetween(0, 10) . ' days');

            $review->setCreatedAt(\DateTimeImmutable::createFromMutable($c));
            $review->setUpdatedAt(\DateTimeImmutable::createFromMutable($u));

            $manager->persist($review);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        // Users, Offers et ExchangeRequests doivent exister
        return [
            UserFixtures::class,
            OfferFixtures::class,
            ExchangeRequestFixtures::class,
        ];
    }
}
