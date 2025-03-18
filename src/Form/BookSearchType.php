<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Book;
use App\Repository\TagRepository;
use App\Repository\BookRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class BookSearchType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {

    $user = $options['user']; // Récupération de l'user
    $isAdult = $user && in_array('ROLE_ADULT', $user->getRoles());

    $builder
      ->add('title', EntityType::class, [
        'class' => Book::class,
        'choice_label' => 'title',
        'autocomplete' => true,
        'required' => false,
        'attr' => ['placeholder' => 'Rechercher par titre'],
        'query_builder' => function (BookRepository $br) use ($isAdult) {
          $qb = $br->createQueryBuilder('b');
          if (!$isAdult) { // Exclure des titres de livres adultes si l'user non co ou n'est pas un adulte
            $qb->where('b.is_for_adult = false');
          }
          return $qb;
        },
      ])
      ->add('author', EntityType::class, [
        'class' => Book::class,
        'choice_label' => 'author',
        'autocomplete' => true,
        'required' => false,
        'attr' => ['placeholder' => 'Nom de l\'auteur'],
        'query_builder' => function (BookRepository $br) use ($isAdult) {
          $qb = $br->createQueryBuilder('bk');
          if (!$isAdult) { // Exclure des auteurs qui n'écrivent que des livres adultes si l'user non co ou n'est pas un adulte
            $qb->where('bk.is_for_adult = false');
          }
          return $qb;
        },
      ])
      ->add('created_within_week', ChoiceType::class, [
        'label' => 'Créé cette semaine ?',
        'required' => false,
        'choices' => ['Oui' => true, 'Non' => false],
      ])
      ->add('published_within_week', ChoiceType::class, [
        'label' => 'Publié cette semaine ?',
        'required' => false,
        'choices' => ['Oui' => true, 'Non' => false],
      ])
      ->add('updated_within_week', ChoiceType::class, [
        'label' => 'Mis à jour cette semaine ?',
        'required' => false,
        'choices' => ['Oui' => true, 'Non' => false],
      ])
      ->add('tags', EntityType::class, [
        'class' => Tag::class,
        'choice_label' => 'name',
        'multiple' => true,
        'expanded' => true,
        'required' => false,
        'label' => 'Tags',
        'query_builder' => function (TagRepository $tr) use ($isAdult) {
          $qb = $tr->createQueryBuilder('t');
          if (!$isAdult) { // Exclure tag Adulte si l'user non co ou n'est pas un adulte
            $qb->where('t.name NOT LIKE :adulte')
              ->setParameter('adulte', 'Adulte');
          }
          return $qb;
        },

      ])
      ->add('matchType', ChoiceType::class, [
        'label' => 'Correspondance des tags',
        'required' => false,
        'choices' => ['Au moins un' => 'any', 'Tous' => 'all'],
      ])
      ->add('excludeTags', EntityType::class, [
        'class' => Tag::class,
        'choice_label' => 'name',
        'label' => 'Tags à exclure',
        'multiple' => true,
        'expanded' => true,
        'required' => false,
        'query_builder' => function (TagRepository $tr) use ($isAdult) {
          $qb = $tr->createQueryBuilder('et');
          if (!$isAdult) { // Exclure tag Adulte si l'user non co ou n'est pas un adulte
            $qb->where('et.name NOT LIKE :adulte')
              ->setParameter('adulte', 'Adulte');
          }
          return $qb;
        },
      ])
      ->add('likes', ChoiceType::class, [
        'label' => 'Populaire',
        'required' => false,
        'choices' => ['Tous' => null, 'Oui' => true, 'Non' => false],
      ])
      ->add('is_published', ChoiceType::class, [
        'label' => 'Publié ?',
        'required' => false,
        'choices' => ['Tous' => null, 'Oui' => true, 'Non' => false],
      ]);

    // Ajouter le champ 'is_for_adult' seulement si l'utilisateur est connecté et a le rôle 'ROLE_ADULT'
    if ($isAdult) {
      $builder->add('is_for_adult', ChoiceType::class, [
        'label' => 'Pour adulte ?',
        'required' => false,
        'choices' => ['Tous' => null, 'Oui' => true, 'Non' => false],
      ]);
    }
    
    $builder->add('orderBy', ChoiceType::class, [
      'label' => 'Trier par',
      'required' => false,
      'choices' => [
        'Date de création' => 'created_at',
        'Date de publication' => 'released_at',
        'Dernière mise à jour' => 'updated_at',
        'Popularité' => 'likes',
        'Alphabétique' => 'title',
        'Auteur' => 'author',
      ],
    ])
      ->add('orderDirection', ChoiceType::class, [
        'label' => 'Ordre',
        'required' => false,
        'choices' => ['Descendant' => 'DESC', 'Ascendant' => 'ASC'],
      ])
      ->add('limit', IntegerType::class, [
        'label' => 'Nombre de résultats',
        'required' => false,
        'attr' => ['min' => 1, 'placeholder' => 'Ex: 20'],
      ])
      ->add('submit', SubmitType::class, [
        'label' => 'Rechercher',
        'attr' => ['class' => 'mt-2 bg-custom-blue text-white py-2 px-4 rounded'],
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'method' => 'GET',
      'user' => null, // user par défaut null
    ]);
  }
}
