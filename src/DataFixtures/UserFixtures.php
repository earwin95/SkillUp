<?php

namespace App\DataFixtures;

use App\Entity\User;
use Faker\Factory;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // 👑 1 admin user
        $admin = new User();
        $admin
            ->setEmail('admin@admin.com')
            ->setUsername('admin')
            ->setPassword($this->hasher->hashPassword($admin, 'adminpass'))
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setBio('Je suis l’administrateur du site.');
        $manager->persist($admin);

        // 👥 20 utilisateurs classiques
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user
                ->setEmail($faker->unique()->safeEmail())
                ->setUsername($faker->userName())
                ->setPassword($this->hasher->hashPassword($user, 'userpass'))
                ->setRoles(['ROLE_USER'])
                ->setBio($faker->optional()->text(200)); // parfois vide, parfois rempli

            $manager->persist($user);
        }

        $manager->flush();
    }
}
