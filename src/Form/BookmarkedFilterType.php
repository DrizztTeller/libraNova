<?php
namespace App\Form;

use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class BookmarkedFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_published', CheckboxType::class, [
                'label' => 'Disponibles uniquement',
                'required' => false
            ])
            ->add('newly_available', CheckboxType::class, [
                'label' => 'Nouveautés disponibles',
                'required' => false,
                'help' => 'Disponibles depuis moins de 7 jours'
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Tags'
            ])
            ->add('sort_by', ChoiceType::class, [
                'label' => 'Trier par',
                'choices' => [
                    'Alphabétique' => 'title',
                    'Auteur' => 'author',
                    'Popularité' => 'popularity',
                    'Date de publication' => 'released_at',
                    'Date de disponibilité' => 'updated_at',
                    'Date d\'ajout' => 'created_at'
                ],
                'required' => true
            ])
            ->add('sort_order', ChoiceType::class, [
                'label' => 'Ordre',
                'choices' => [
                    'Ascendant' => 'ASC',
                    'Descendant' => 'DESC'
                ],
                'required' => true,
                'data' => 'DESC' 
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }
}
