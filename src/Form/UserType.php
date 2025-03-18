<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => "Votre nom d'utilisateur",
                'label_attr' => ['class' => 'block text-sm font-medium text-gray-700 mb-1'],
                'attr' => [
                    'class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nouveau nom d\'utilisateur']),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre prénom doit faire au moins {{ limit }} caractères',
                        'max' => 50,
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => "Votre adresse e-mail",
                'label_attr' => ['class' => 'block text-sm font-medium text-gray-700 mb-1'],
                'attr' => [
                    'class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
                ],
                'constraints' => [
                    new NotBlank(['message' => "Veuillez entrer une adresse email"]),
                    new Email(['message' => "L'adresse email {{ value }} n'est pas valide"]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Saisisez votre mot de passe pour mettre à jour votre profil',
                'toggle' => true,
                'visible_icon' => '🐵',
                'hidden_icon' => '🙈',
                'label_attr' => ['class' => 'block text-sm font-medium text-gray-700 mb-1'],
                'attr' => [
                    'placeholder' => "Mot de passe actuel",
                    'class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe'
                    ]),
                    new Length([
                        'min' => 12,
                        'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                    new Regex(
                        [
                            'pattern' => '/^(?=.*[!@#$%^*-])(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])\S{12,}$/',
                            'message' => "Le mot de passe doit être composé d'au moins 12 caractères consécutifs, sans espace, et doit contenir au moins une lettre Majuscule, une lettre minuscule, un chiffre et un caractère spécial parmis ! @ # $ % ^ * -"
                        ]
                    )
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'options' => ['row_attr' => ['class' => 'mb-3']],
                'first_options' => [
                    'label' => 'Votre nouveau Mot de passe',
                    'label_attr' => ['class' => 'block text-sm font-medium text-gray-700 mb-1'],
                    'attr' => [
                        'class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'label_attr' => ['class' => 'block text-sm font-medium text-gray-700 mb-1'],
                    'attr' => [
                        'class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
                    ],
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir un mot de passe',
                    ]),
                    new Length([
                        'min' => 12,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                    new Regex(
                        [
                            'pattern' => '/^(?=.*[!@#$%^*-])(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])\S{12,}$/',
                            'message' => "Le mot de passe doit être composé d'au moins 12 caractères consécutifs, sans espace, et doit contenir au moins une lettre Majuscule, une lettre minuscule, un chiffre et un caractère spécial parmis ! @ # $ % ^ * -"
                        ]
                    )
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Modifier mon profil',
                'attr' => ['class' => 'mt-2 bg-custom-blue text-white py-2 px-4 rounded'],
            ])
            //             ->add('books', EntityType::class, [
            //                 'class' => Book::class,
            // 'choice_label' => 'id',
            // 'multiple' => true,
            //             ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
