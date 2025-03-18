<?php

namespace App\Form;

use App\Entity\RentingHistory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RentingHistoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('last_page', TextType::class, [
                'constraints' => [
                    new Regex(
                        [
                            'pattern' => '/^\d+$|^terminé$/i',
                            'message' => "Veuillez mettre le numéro de la page ou écrire 'terminé' si vous avez fini de lire le livre"
                        ]
                    )
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'mt-2 bg-custom-blue text-white py-2 px-4 rounded'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 'data_class' => RentingHistory::class,
        ]);
    }
}
