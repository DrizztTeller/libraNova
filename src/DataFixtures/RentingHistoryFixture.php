<?php

namespace App\DataFixtures;

use App\Entity\RentingHistory;
use App\Entity\Novel;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RentingHistoryFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        // Récupérer tous les utilisateurs et romans
        $userRepo = $manager->getRepository(User::class);
        $users = $userRepo->findAll();
        
        $novelRepo = $manager->getRepository(Novel::class);
        $novels = $novelRepo->findAll();
        
        // Création d'historiques de location aléatoires
        foreach ($users as $user) {
            // Chaque utilisateur loue entre 1 et 5 livres
            $rentCount = $user->getRentedNovelsCount();
            
            for ($i = 0; $i < $rentCount; $i++) {
                $renting = new RentingHistory();
                
                // Attribuer un utilisateur
                $renting->setUser($user);
                
                // Attribuer un roman aléatoire
                $novel = $faker->randomElement($novels);
                $renting->setNovel($novel);
                
                // Dates de début et de fin
                $startDate = $faker->dateTimeBetween('-6 months', 'now');
                $start = \DateTimeImmutable::createFromMutable($startDate);
                $renting->setStart($start);
                
                // La fin est soit dans le futur (location en cours) soit dans le passé (terminée)
                $isActive = $faker->boolean(70); // 70% des locations sont actives
                if ($isActive) {
                    $endDate = $faker->dateTimeBetween('+1 day', '+30 days');
                } else {
                    $endDate = $faker->dateTimeBetween($startDate, 'now');
                    // TODO $start modify + 5jours
                }
                $end = \DateTimeImmutable::createFromMutable($endDate);
                $renting->setEnd($end);
                
                // Dernière page lue
                if ($isActive) {
                    $renting->setLastPage($faker->numberBetween(1, 500));
                    $renting->setUpdatedAt(new \DateTimeImmutable('-' . $faker->numberBetween(1, 30) . ' days'));
                }
                
                $manager->persist($renting);
            }
        }
        
        $manager->flush();
    }
    
    public function getDependencies():array
    {
        return [
            UserFixture::class,
            NovelFixture::class
        ];
    }
}
