<?php

namespace App\Form;

use App\Enum\ModeContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AnnulationCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('modeContact', EnumType::class, [
                'class'        => ModeContact::class,
                'label'        => 'Mode de contact avec le client',
                'choice_label' => fn(ModeContact $m) => $m->label(),
                'placeholder'  => 'Comment avez-vous contacté le client ?',
                'constraints'  => [
                    new Assert\NotNull(message: 'Le mode de contact est obligatoire.'),
                ],
            ])
            ->add('motif', TextareaType::class, [
                'label' => 'Motif',
                'attr'  => ['rows' => 4, 'placeholder' => 'Expliquez la raison de l\'annulation...'],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le motif est obligatoire.'),
                    new Assert\Length(min: 10, minMessage: 'Le motif doit faire au moins 10 caractères.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
