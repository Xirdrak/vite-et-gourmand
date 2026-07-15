<?php

namespace App\Form;

use App\Entity\Avis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('note', IntegerType::class, [
                'label' => 'Note',
                'attr'  => ['min' => 1, 'max' => 5, 'id' => 'note-input'],
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez attribuer une note.'),
                    new Assert\Range(
                        min: 1,
                        max: 5,
                        notInRangeMessage: 'La note doit être comprise entre 1 et 5.',
                    ),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Votre commentaire',
                'attr'  => ['rows' => 5, 'placeholder' => 'Partagez votre expérience...'],
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez rédiger un commentaire.'),
                    new Assert\Length(
                        min: 10,
                        max: 1000,
                        minMessage: 'Le commentaire doit faire au moins {{ limit }} caractères.',
                        maxMessage: 'Le commentaire ne peut pas dépasser {{ limit }} caractères.',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
        ]);
    }
}
