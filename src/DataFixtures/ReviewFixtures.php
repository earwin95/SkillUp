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

        // ✅ Récupère en BDD toutes les demandes ACCEPTED
        /** @var ExchangeRequest[] $acceptedRequests */
        $acceptedRequests = $manager->getRepository(ExchangeRequest::class)
            ->findBy(['status' => ExchangeRequestStatus::ACCEPTED]);

        // 1) Reviews liées à des échanges ACCEPTED (dans les deux sens, souvent)
        foreach ($acceptedRequests as $er) {
            $requester = $er->getRequester();
            $owner     = $er->getOffer()?->getOwner();

            if (!$requester || !$owner || $requester->getId() === $owner->getId()) {
                continue;
            }

            // a) requester -> owner
            $r1 = (new Review())
                ->setAuthor($requester)
                ->setSubjectUser($owner)
                ->setOffer($er->getOffer())
                ->setExchangeRequest($er)
                ->setRating($faker->numberBetween(4, 5))
                ->setComment($faker->optional(0.8)->paragraph());

            $createdAt = $faker->dateTimeBetween($er->getCreatedAt()->format('Y-m-d H:i:s'), 'now');
            $updatedAt = (clone $createdAt)->modify('+' . $faker->numberBetween(0, 7) . ' days');

            $r1->setCreatedAt(\DateTimeImmutable::createFromMutable($createdAt));
            $r1->setUpdatedAt(\DateTimeImmutable::createFromMutable($updatedAt));

            $manager->persist($r1);

            // b) owner -> requester (70% des cas)
            if ($faker->boolean(70)) {
                $r2 = (new Review())
                    ->setAuthor($owner)
                    ->setSubjectUser($requester)
                    ->setOffer($er->getOffer())
                    ->setExchangeRequest($er)
                    ->setRating($faker->numberBetween(4, 5))
                    ->setComment($faker->optional(0.7)->paragraph());

                $c2 = (clone $createdAt)->modify('+' . $faker->numberBetween(0, 3) . ' days');
                $u2 = (clone $c2)->modify('+' . $faker->numberBetween(0, 5) . ' days');

                $r2->setCreatedAt(\DateTimeImmutable::createFromMutable($c2));
                $r2->setUpdatedAt(\DateTimeImmutable::createFromMutable($u2));

                $manager->persist($r2);
            }
        }

        // 2) Quelques reviews "libres" (pas forcément liées à un échange)
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
                $i--; // rejouer ce tour
                continue;
            }

            $review = (new Review())
                ->setAuthor($author)
                ->setSubjectUser($subject)
                ->setRating($faker->numberBetween(3, 5))
                ->setComment($faker->optional(0.7)->paragraph());

            if ($offers && $faker->boolean(60)) {
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
        // Besoin que Users, Offers et ExchangeRequests soient déjà en BDD
        return [
            UserFixtures::class,
            OfferFixtures::class,
            ExchangeRequestFixtures::class,
        ];
    }
}
