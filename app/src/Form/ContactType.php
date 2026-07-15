<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label'       => 'Votre adresse e-mail',
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez indiquer votre adresse e-mail.'),
                    new Assert\Email(message: 'Adresse e-mail invalide.'),
                    new Assert\Length(max: 180),
                ],
            ])
            ->add('sujet', TextType::class, [
                'label'       => 'Sujet',
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez indiquer un sujet.'),
                    new Assert\Length(max: 150, maxMessage: 'Le sujet ne peut pas depasser {{ limit }} caracteres.'),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label'       => 'Message',
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez écrire votre message.'),
                    new Assert\Length(
                        min: 10,
                        max: 2000,
                        minMessage: 'Le message doit contenir au moins {{ limit }} caracteres.',
                        maxMessage: 'Le message ne peut pas depasser {{ limit }} caracteres.',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
