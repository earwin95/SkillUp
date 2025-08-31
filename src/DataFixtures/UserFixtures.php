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
    public const NB_USERS   = 60; // ⇦ on passe à 60 utilisateurs

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // données réalistes en français

        // 👑 Admin
        $admin = new User();
        $admin
            ->setEmail('admin@admin.com')
            ->setUsername('admin')
            ->setPassword($this->hasher->hashPassword($admin, 'adminpass'))
            ->setRoles(['ROLE_ADMIN'])
            ->setBio('Je suis l’administrateur du site SkillUp, prêt à superviser les échanges de compétences.');
        $manager->persist($admin);
        $this->addReference(self::REF_PREFIX.'admin', $admin);

        // 👥 Utilisateurs classiques
        for ($i = 1; $i <= self::NB_USERS; $i++) {
            $user = new User();

            $prenom = $faker->firstName();
            $nom    = $faker->lastName();

            $user
                ->setEmail(strtolower($prenom.'.'.$nom).'@'.$faker->freeEmailDomain())
                ->setUsername($faker->unique()->userName())
                ->setPassword($this->hasher->hashPassword($user, 'userpass'))
                ->setRoles([]) // ROLE_USER ajouté automatiquement
                ->setBio($faker->sentence(10) . ' ' . $faker->sentence(12)); // bio + naturelle

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
