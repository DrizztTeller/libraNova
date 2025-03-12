<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TagFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
            $faker = Factory::create('fr_FR'); // Chargement de Faker
            
            $tagNames = [
                'Roman',
                'Science-Fiction',
                'Biographie',
                'Histoire',
                'Jeunesse',
                'Technique',
                'Manga',
                'Adulte',
            ];

            foreach ($tagNames as $index => $name) {
                $tag = new Tag();
                $tag->setName($name);
                $tag->setDescription($faker->paragraph());
                $manager->persist($tag);
                // Définir une référence pour pouvoir l'utiliser dans d'autres fixtures
                $this->addReference('tag_' . $index, $tag);
                $this->addReference('tag_' . $name, $tag); // Référence par nom pour faciliter l'accès

            }
            // for ($i = 0; $i < count($categories); $i++) {
            //     $category = new Category();
            //     $category
            //         ->setName($categories[$i])
            //         ->setImage('https://picsum.photos/300/300?random=' . $i)
            //     ;
            //     $this->addReference('category_' . $i, $category); // Ajoute une référence
            //     $manager->persist($category); // Ajoute à la BDD
            // }

        $manager->flush();
    }
}
