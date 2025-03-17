<?php

namespace App\DataFixtures;

use App\Entity\RentingHistory;
use App\Entity\Book;
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

        $bookRepo = $manager->getRepository(Book::class);
        $books = $bookRepo->findAll();

        // Création d'historiques de location aléatoires
        foreach ($users as $user) {
            // Chaque utilisateur loue entre 1 et 5 livres
            $rentCount = $user->getRentedBooksCount();

            for ($i = 0; $i < $rentCount; $i++) {
                $renting = new RentingHistory();

                // Attribuer un utilisateur
                $renting->setUser($user);

                // Attribuer un roman aléatoire
                $book = $faker->randomElement($books);
                $renting->setBook($book);

                // Dates de début et de fin
                $startDate = $faker->dateTimeBetween('-6 months', 'now');
                $start = \DateTimeImmutable::createFromMutable($startDate);
                $renting->setStart($start);

                // La fin d'emprunt est 5 jours plus tard
                $endDate = clone $startDate;
                $endDate->modify("+5 days");
                $end = \DateTimeImmutable::createFromMutable($endDate);
                $renting->setEnd($end);

                // Dernière page lue
                $now = new \DateTimeImmutable();
                if ($end > $now) {
                    $renting->setLastPage((string) $faker->numberBetween(1, 500));
                    $renting->setUpdatedAt($now);
                } else {
                    $isFinished = $faker->boolean(50);
                    if ($isFinished) {
                        $renting->setLastPage("terminé");
                    } else {
                        $renting->setLastPage((string) $faker->numberBetween(1, 500));
                    }
                    $renting->setUpdatedAt($end);
                }

                $manager->persist($renting);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            BookFixture::class
        ];
    }
}
