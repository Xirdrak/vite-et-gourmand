<?php

namespace App\Form;

use App\Entity\Horaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class HoraireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heureOuverture', TimeType::class, [
                'label'  => 'Ouverture',
                'widget' => 'single_text',
                'html5'  => true,
                'constraints' => [
                    new Assert\NotNull(message: "L'heure d'ouverture est requise."),
                ],
            ])
            ->add('heureFermeture', TimeType::class, [
                'label'  => 'Fermeture',
                'widget' => 'single_text',
                'html5'  => true,
                'constraints' => [
                    new Assert\NotNull(message: "L'heure de fermeture est requise."),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Horaire::class,
        ]);
    }
}
