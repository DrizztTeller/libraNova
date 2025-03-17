<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Votre adresse e-mail',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'placeholder' => 'exemple@gmail.com',
                    'class' => 'form-control',
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'options' => ['row_attr' => ['class' => 'mb-3']],
                'first_options' => [
                    'label' => 'Mot de passe',
                    'label_attr' => ['class' => 'form-label'],
                    'attr' => ['class' => 'form-control mb-3'],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'label_attr' => ['class' => 'form-label'],
                    'attr' => ['class' => 'form-control mb-3'],
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
            ->add('username', TextType::class, [
                'row_attr' => ['class' => 'mb-3'],
                'label' => "Votre nom d'utilisateur",
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom d\'utilisateur'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre nom d\'utilisateur doit faire au moins {{ limit }} caractères',
                        'max' => 50,
                        'maxMessage' => 'Votre nom d\'utilisateur ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('is_adult', CheckboxType::class, [
                'row_attr' => ['class' => 'form-check mb-2'],
                'label' => 'Vous confirmez que vous êtes majeur',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input'],
                'required' => false,
            ])
            ->add('is_terms', CheckboxType::class, [
                'row_attr' => ['class' => 'form-check mb-2'],
                'label' => "J'accepte <a class='underline' href='" . $options['cgu_url'] . "' target='_blank'>les conditions d'utilisation</a>", // Ajout du lien dans le label et Utilisation de l'URL générée dans le RegistrationController
                'label_html' => true, // Permet l'interprétation du HTML dans le label
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input'],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les CGU pour vous inscrire',
                    ]),
                ],
                'data' => true,
            ])
            ->add('is_gpdr', CheckboxType::class, [
                'row_attr' => ['class' => 'form-check mb-2'],
                'label' => "J'accepte <a class='underline' href='" . $options['rgpd_url'] . "' target='_blank'>la politique RGPD de LibraNova</a>", // Ajout du lien dans le label et et Utilisation de l'URL générée
                'label_html' => true, // Permet l'interprétation du HTML dans le label
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input'],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter notre politique RGPD pour vous inscrire',
                    ]),
                ],
                'data' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => "S'inscrire",
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'cgu_url' => null, // Par défaut, l'URL est null
            'rgpd_url' => null, // Par défaut, l'URL est null
        ]);
    }
}
