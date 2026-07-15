<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CommandeModificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $commande = $options['commande'];

        $builder
            ->add('nombre_personne', IntegerType::class, [
                'label' => 'Nombre de personnes',
                'data'  => $commande->getNombrePersonne(),
                'attr'  => ['min' => 1],
                'constraints' => [
                    new Assert\NotBlank(message: 'Indiquez le nombre de personnes.'),
                    new Assert\Positive(message: 'Le nombre doit être positif.'),
                ],
            ])
            ->add('date_prestation', DateType::class, [
                'label'  => 'Date de la prestation',
                'widget' => 'single_text',
                'html5'  => true,
                'data'   => $commande->getDatePrestation(),
                'constraints' => [
                    new Assert\NotBlank(message: 'La date de prestation est requise.'),
                    new Assert\GreaterThan('today', message: 'La date doit être dans le futur.'),
                ],
            ])
            ->add('heure_livraison', TimeType::class, [
                'label'  => 'Heure de livraison',
                'widget' => 'single_text',
                'html5'  => true,
                'data'   => $commande->getHeureLivraison(),
                'constraints' => [
                    new Assert\NotBlank(message: "L'heure de livraison est requise."),
                ],
            ])
            ->add('adresse_livraison', TextType::class, [
                'label' => 'Adresse de livraison',
                'data'  => $commande->getAdresseLivraison(),
                'constraints' => [
                    new Assert\NotBlank(message: "L'adresse est requise."),
                    new Assert\Length(max: 255),
                ],
            ])
            ->add('ville_livraison', TextType::class, [
                'label' => 'Ville',
                'data'  => $commande->getVilleLivraison(),
                'constraints' => [
                    new Assert\NotBlank(message: 'La ville est requise.'),
                    new Assert\Length(max: 100),
                ],
            ])
            ->add('hors_bordeaux', CheckboxType::class, [
                'label'    => 'Livraison hors Bordeaux (supplement)',
                'required' => false,
                'data'     => $options['hors_bordeaux_initial'],
            ])
            ->add('nombre_km', IntegerType::class, [
                'label'    => 'Distance estimee (en km)',
                'required' => false,
                'data'     => $options['nombre_km_initial'] > 0 ? $options['nombre_km_initial'] : null,
                'attr'     => ['min' => 1],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('commande');
        $resolver->setDefaults([
            'hors_bordeaux_initial' => false,
            'nombre_km_initial'     => 0,
        ]);
    }
}
