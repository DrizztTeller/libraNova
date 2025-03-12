<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class Tag extends Fixture
{
    public function load(ObjectManager $manager): void
    {
            $faker = Factory::create('fr_FR'); // Chargement de Faker
            $tags = [];
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

            foreach ($tagNames as $name) {
                $tag = new Tag();
                $tag->setName($name);
                $manager->persist($tag);
                $tags[] = $tag;

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
