<?php

namespace App\DataFixtures;

use App\Entity\Novel;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class NovelFixture extends Fixture //implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Chargement de Faker

             // Créer des livres
        for ($i = 0; $i < 50; $i++) {
            $novel = new Novel();
            $novel->setName($faker->sentence(3));
            $novel->setDescription($faker->paragraphs());
            $novel->setAbstract($faker->paragraphs(50));
            $novel->setPublicationDate($faker->dateTimeBetween('-30 years', 'now'));
            $novel->setPageCount($faker->numberBetween(50, 1200));
            $novel->setPublishedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeThisDecade()));
            $novel->setIsbn($faker->isbn13());
            
            // Attribuer un auteur aléatoire
            $authorIndex = $faker->numberBetween(0, 9);
            $novel->setAuthor($this->getReference('author_' . $authorIndex));

            // Ajouter entre 1 et 3 tags aléatoires
            $tagCount = $faker->numberBetween(1, 3);
            $tagIndexes = $faker->randomElements(range(0, 7), $tagCount);
            
            foreach ($tagIndexes as $tagIndex) {
                $novel->addTag($this->getReference('tag_' . $tagIndex));
            }
            
            
            $manager->persist($novel);

        $manager->flush();
        
        }
    }
    public function getDependencies()
    {
        return [
            AuthorFixture::class,
            ClientFixture::class,
            TagFixture::class,
        ];
    }
}
