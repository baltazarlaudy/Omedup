<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sexe', ChoiceType::class, [
                'choices' =>[
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                    'Autre' => 'Autre'
                ]
            ])
            ->add('lastName', TextType::class)
            ->add('firstName', TextType::class)
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'Les emails doivent etre identique',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'S\'il vous plait entrer votre email'
                    ])
                ],
                'first_options' => ['label' =>'Entrer votre email'],
                'second_options' => ['label' => 'Confirmer votre email' ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type' => PasswordType::class,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'S\'il vous plait entrer un mot de pass valide',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de pass doit avoir au moins {{ limit }} characteres',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'first_options' => ['label' =>'Entrer votre mot de pass'],
                'second_options' => ['label' => 'Confirmer votre mot de pass' ]
            ])
            ->add('birthDay', BirthdayType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
