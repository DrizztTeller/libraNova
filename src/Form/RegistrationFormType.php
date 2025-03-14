<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
                    'placeholder' => 'libranova@gmail.com',
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
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('isMajor', CheckboxType::class, [
                'mapped' => false,
                'row_attr' => ['class' => 'form-check mb-2'],
                'label' => 'Vous confirmez que vous êtes majeur',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input'],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez être majeur pour vous inscrire',
                    ]),
                ],
            ])
            ->add('isTerms', CheckboxType::class, [
                'mapped' => false,
                'row_attr' => ['class' => 'form-check mb-2'],
                'label' => "J'accepte les CGU et CGV",
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input'],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les CGU pour vous inscrire',
                    ]),
                ],
            ])
            ->add('isGpdr', CheckboxType::class, [
                'mapped' => false,
                'row_attr' => ['class' => 'form-check mb-2'],
                'label' => "J'accepte la politique RGPD de miniamaker",
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input'],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter notre politique RGPD pour vous inscrire',
                    ]),
                ],
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
        ]);
    }
}
