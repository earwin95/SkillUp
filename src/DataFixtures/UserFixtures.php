<?php

namespace App\DataFixtures;

use App\Entity\User;
use Faker\Factory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public const REF_PREFIX = 'user_';
    public const NB_USERS   = 20; // ⇦ modifie si besoin

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
            ->setRoles(['ROLE_ADMIN']) // ROLE_USER sera ajouté automatiquement dans getRoles()
            ->setBio('Je suis l’administrateur du site.');
        $manager->persist($admin);
        $this->addReference(self::REF_PREFIX.'admin', $admin);

        // 👥 utilisateurs classiques
        for ($i = 1; $i <= self::NB_USERS; $i++) {
            $user = new User();
            $user
                ->setEmail($faker->unique()->safeEmail())
                ->setUsername($faker->unique()->userName())
                ->setPassword($this->hasher->hashPassword($user, 'userpass'))
                ->setRoles([]) // ROLE_USER ajouté automatiquement par getRoles()
                ->setBio($faker->optional()->text(200));

            $manager->persist($user);
            $this->addReference(self::REF_PREFIX.$i, $user);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['dev'];
    }
}
