<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('plainPassword', RepeatedType::class, [
            'type'            => PasswordType::class,
            'mapped'          => false,
            'first_options'   => ['label' => 'Nouveau mot de passe'],
            'second_options'  => ['label' => 'Confirmer le mot de passe'],
            'invalid_message' => 'Les mots de passe ne correspondent pas.',
            'constraints'     => [
                new Assert\NotBlank(),
                new Assert\Length(min: 10, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caracteres.'),
                new Assert\Regex(pattern: '/[A-Z]/', message: 'Le mot de passe doit contenir au moins une majuscule.'),
                new Assert\Regex(pattern: '/[a-z]/', message: 'Le mot de passe doit contenir au moins une minuscule.'),
                new Assert\Regex(pattern: '/[0-9]/', message: 'Le mot de passe doit contenir au moins un chiffre.'),
                new Assert\Regex(pattern: '/[^a-zA-Z0-9]/', message: 'Le mot de passe doit contenir au moins un caractere special.'),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
