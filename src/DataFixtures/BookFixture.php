<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class BookFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            TagFixture::class,
            UserFixture::class,
        ];
    }
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger, private TagRepository $tr)
    {
        $this->slugger = $slugger;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Chargement de Faker
        
        $authors = [
            'J.K. Rowling',
            'Stephen King',
            'George R.R. Martin',
            'Haruki Murakami',
            'Margaret Atwood',
            'Neil Gaiman',
            'Chimamanda Ngozi Adichie',
            'Jane Austen',
            'Gabriel García Márquez',
            'Toni Morrison'
        ];

        // Liste des titres selon le type
        $titlePrefixes = [
            'Roman' => 'Bridget Jones - ',
            'Science-Fiction' => 'Star Wars - ',
            'Biographie' => 'La Vie de ',
            'Histoire' => 'Histoire de ',
            'Jeunesse' => 'Les Aventures de ',
            'Technique' => 'Guide de ',
            'Manga' => 'One Piece - ',
            'Adulte' => '50 shades of'
        ];

        $allTags = $this->tr->findAll();
        $adultTag = $this->tr->findOneBy(['name' => 'Adulte']);
        
             // Créer 50 livres
        for ($i = 0; $i < 20; $i++) {
            $book = new Book($this->slugger);

            //Selection de tags
            $tagCount = $faker->numberBetween(1, 3);
            $selectedTags = $faker->randomElements($allTags, $tagCount);

            // Déterminer un titre en fonction du premier tag
            $firstTag = $selectedTags[0];

            $tagName = $firstTag->getName();
            $prefix = $titlePrefixes[$tagName] ?? '';
            $title = $prefix . $faker->words(3, true);

            $book->setTitle($title);
            $book->setAuthor($faker->randomElement($authors));  // Attribuer un auteur aléatoire
            $book->setAbstract($faker->paragraphs(3, true));
            $book->setIsPublished($faker->boolean(70));
            $book->setReleasedAt($faker->dateTimeThisDecade());

            // Images et fichiers fictifs
            
            // Images avec des IDs fixes (choisies pour ressembler à des couvertures de livres)
            $bookCoverIds = [20, 24, 42, 67, 101, 180, 240, 251, 292, 331, 373, 384];
            $randomBookCoverId = $bookCoverIds[array_rand($bookCoverIds)];
            $book->setPicUrl('https://picsum.photos/id/' . $randomBookCoverId . '/800/600');
            
            // Slug et référence
            $slug = $this->slugger->slug($title)->lower();
            $book->setSlug($slug);
            $book->setRef($slug . '-' . $faker->unique()->numerify('######'));
            $book->setIsbn($faker->isbn13());
            $book->setIsForAdult($faker->boolean(20));
            // Ajouter les tags au roman
            foreach ($selectedTags as $tag) {
                $book->addTag($tag);
                if ($book->isForAdult()) {
                    $book->addTag($adultTag);
                }
            }            
            $manager->persist($book);
        }
        $manager->flush();
        
        }

}
