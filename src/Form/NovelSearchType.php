<?php

namespace App\Form;

use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NovelSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Rechercher par titre',
                    'class' => 'w-full p-2 border rounded mb-3'
                ],
            ])
            ->add('author', TextType::class, [
                'required' => false,
                'label' => 'Auteur',
                'attr' => [
                    'placeholder' => 'Rechercher par auteur',
                    'class' => 'w-full p-2 border rounded mb-3'
                ],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'label' => 'Tags',
                'attr' => [
                    'class' => 'w-full p-2 border rounded mb-3'
                ],
            ])
            ->add('orderBy', ChoiceType::class, [
                'required' => false,
                'label' => 'Trier par',
                'choices' => [
                    'Date d\'ajout' => 'created_at',
                    'Date de publication' => 'released_at',
                    'Popularité' => 'likes',
                    'Titre' => 'title',
                ],
                'data' => 'released_at',
                'attr' => [
                    'class' => 'w-full p-2 border rounded mb-3'
                ],
            ])
            ->add('orderDirection', ChoiceType::class, [
                'required' => false,
                'label' => 'Direction',
                'choices' => [
                    'Décroissant' => 'DESC',
                    'Croissant' => 'ASC',
                ],
                'data' => 'DESC',
                'expanded' => true,
                'attr' => [
                    'class' => 'mb-3'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
