<?php


namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Chargement de Faker

         // Créer un admin
         $admin = new User();
         $admin->setEmail('admin@admin.com');
         $admin->setRoles(['ROLE_ADMIN','ROLE_VERIFIED', 'ROLE_ADULT' ]);
         $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
         $admin->setUsername('MFT');
         $admin->setIsTerms(true);
         $admin->setIsGpdr(true);
         $admin->setIsVerified(true);
         $admin->setIsAdult(true);
         $admin->setRentedNovelsCount(0);
         $admin->setRef('ADMIN-' . $faker->unique()->numerify('######'));
         
         
         $manager->persist($admin);
        //création de 10 users
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email());
            $user->setPassword($this->passwordHasher->hashPassword($user, 'admin123'));
            $user->setUsername($faker->username);
            $user->setRoles(['ROLE_USER']);
            $user->setIsAdult($faker->boolean(70)); // 70% sont adultes
            $user->setRentedNovelsCount($faker->numberBetween(1, 5));// Nombre aléatoire entre 1 et 5 de livre loué
         //   $user->setLikedNovelsCount($faker->numberBetween(1, 10));// Nombre aléatoire entre 1 et 10 livre loué
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
