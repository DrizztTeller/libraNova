<?php

namespace App\DataFixtures;

use App\Entity\Novel;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class NovelFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            TagFixture::class,
            UserFixture::class,
        ];
    }
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
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

        // // Récupérer tous les tags
        // $tagRepo = $manager->getRepository(Tag::class);
        // $allTags = $tagRepo->findAll();

        // Récupérer une liste de tags via les références créées dans TagFixture
        $allTags = [];
        for ($i = 0; $i < 8; $i++) { // Pour 8 tags
            try {
                $tag = $this->getReference('tag_' . $i, Tag::class);
                $allTags[] = $tag;
            } catch (\Exception $e) {
                // Gérer l'exception ou l'ignorer silencieusement
                dump("Tag référence 'tag_" . $i . "' non trouvé");
            }
        }
        
             // Créer 50 livres
        for ($i = 0; $i < 20; $i++) {
            $novel = new Novel();

            //Selection de tags
            $tagCount = $faker->numberBetween(1, 3);
            $selectedTags = $faker->randomElements($allTags, $tagCount);

            foreach ($selectedTags as $tag) {
                $novel->addTag($tag); // Liaison des tags avec le roman
            }

            // Déterminer un titre en fonction du premier tag
            $firstTag = $selectedTags[0]; // Obtenir le premier tag
            $tagNames = $firstTag->getName(); // Obtenir le nom du premier tag
            $prefix = $titlePrefixes[$tagNames] ?? ''; // Utiliser le prefixe par défaut
            $title = $prefix . $faker->words(3, true); // Attribuer un titre aléatoire

            $novel->setTitle($title);
            $novel->setAuthor($faker->randomElement($authors));  // Attribuer un auteur aléatoire
            $novel->setAbstract($faker->paragraphs(3, true));
            $novel->setIsPublished(true, 70);
            $novel->setReleasedAt($faker->dateTimeThisDecade());
            $novel->setCreatedAt(new \DateTimeImmutable());


            // Images et fichiers fictifs
            
            // Images avec des IDs fixes (choisies pour ressembler à des couvertures de livres)
            $bookCoverIds = [20, 24, 42, 67, 101, 180, 240, 251, 292, 331, 373, 384];
            $randomBookCoverId = $bookCoverIds[array_rand($bookCoverIds)];
            $novel->setPicUrl('https://picsum.photos/id/' . $randomBookCoverId . '/800/600');
            // TODO remplacer par réel fichier
            $novel->setFile('book_' . $faker->numberBetween(1, 20) . '.pdf');
        
            
            // Slug et référence
            $slug = $this->slugger->slug($title)->lower();
            $novel->setSlug($slug);
            $novel->setRef($slug . '-' . $faker->unique()->numerify('######'));
            $novel->setIsbn($faker->isbn13());
            $novel->setIsForAdult($faker->boolean(30));

            // TODO si is_for_adult true faut absolument tag adulte
            
            $manager->persist($novel);
        }
        $manager->flush();
        
        }

}
            // $authorIndex = $faker->numberBetween(0, 9);
            // $novel->setAuthor($this->getReference('author_' . $authorIndex));

            // Ajouter entre 1 et 3 tags aléatoires
            // $tagCount = $faker->numberBetween(1, 3);
            // $tagIndexes = $faker->randomElements(range(0, 7), $tagCount);
            
            // foreach ($tagIndexes as $tagIndex) {
            //     $novel->addTag($this->getReference('tag_' . $tagIndex));
            // }
