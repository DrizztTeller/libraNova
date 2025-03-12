<?php


namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Chargement de Faker

        //création de 10 users
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setPassword($faker->password);
            $user->setUsername($faker->username);
            $user->setRoles(['ROLE_USER']);
            $user->setIsAdult($faker->boolean(70)); // 70% sont adultes
            $user->setRentedNovelsCount($faker->numberBetween(1, 5));// Nombre aléatoire entre 1 et 5 de livre loué
            $user->setLikedNovelsCount($faker->numberBetween(1, 10));// Nombre aléatoire entre 1 et 10 livre loué
            $user->setRef('USER-' . $faker->unique()->numerify('######')); //création d'un numéro pour chaque user
            $user->setIsVerified(true);
            $user->setIsTerms(true);
            $user->setIsGpdr(true);

            $manager->persist($user);

            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);
            
            $manager->persist($user);
            
            // Ajouter une référence
            $this->addReference('user_' . $i, $user);
        }

        $manager->flush();
    }
}
