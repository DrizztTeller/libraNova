<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class BookSearchType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('title', EntityType::class, [
        'class' => Book::class,
        'choice_label' => 'title',
        'autocomplete' => true,
        'required' => false,
        'attr' => ['placeholder' => 'Rechercher par titre'],
      ])
      ->add('author', EntityType::class, [
        'class' => Book::class,
        'choice_label' => 'author',
        'autocomplete' => true,
        'required' => false,
        'attr' => ['placeholder' => 'Nom de l\'auteur'],
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
        'label' => 'Tags'
      ])
      ->add('matchType', ChoiceType::class, [
        'label' => 'Correspondance des tags',
        'required' => false,
        'choices' => ['Au moins un' => 'any', 'Tous' => 'all'],
      ])
      ->add('excludeTags', TextType::class, [
        'label' => 'Tags à exclure (séparés par des virgules)',
        'required' => false,
        'attr' => ['placeholder' => 'Ex: horreur, drame'],
      ])
      ->add('likes', IntegerType::class, [
        'label' => 'Nombre minimum de likes',
        'required' => false,
        'attr' => ['min' => 0, 'placeholder' => 'Ex: 10'],
      ])
      ->add('is_published', ChoiceType::class, [
        'label' => 'Publié ?',
        'required' => false,
        'choices' => ['Tous' => null, 'Oui' => true, 'Non' => false],
      ])
      ->add('is_for_adult', ChoiceType::class, [
        'label' => 'Pour adulte ?',
        'required' => false,
        'choices' => ['Tous' => null, 'Oui' => true, 'Non' => false],
      ])
      ->add('orderBy', ChoiceType::class, [
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
        'attr' => ['class' => 'btn btn-primary'],
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'method' => 'GET',
    ]);
  }
}
