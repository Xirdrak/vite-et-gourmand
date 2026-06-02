<?php

namespace App\Form;

use App\Entity\Menu;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('menu', EntityType::class, [
                'class'        => Menu::class,
                'choice_label' => 'titre',
                'label'        => 'Menu',
                'placeholder'  => 'Choisissez un menu',
                'data'         => $options['menu_preselectionne'],
                'query_builder' => function ($repo) {
                    return $repo->createQueryBuilder('m')
                        ->where('m.actif = true')
                        ->andWhere('m.quantiteRestante > 0')
                        ->orderBy('m.titre', 'ASC');
                },
                'constraints' => [
                    new Assert\NotNull(message: 'Veuillez choisir un menu.'),
                ],
            ])
            ->add('nombre_personne', IntegerType::class, [
                'label' => 'Nombre de personnes',
                'attr'  => ['min' => 1, 'id' => 'nombre_personne'],
                'constraints' => [
                    new Assert\NotBlank(message: 'Indiquez le nombre de personnes.'),
                    new Assert\Positive(message: 'Le nombre doit etre positif.'),
                ],
            ])
            ->add('date_prestation', DateType::class, [
                'label'  => 'Date de la prestation',
                'widget' => 'single_text',
                'html5'  => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'La date de prestation est requise.'),
                    new Assert\GreaterThan('today', message: 'La date doit etre dans le futur.'),
                ],
            ])
            ->add('heure_livraison', TimeType::class, [
                'label'  => 'Heure de livraison',
                'widget' => 'single_text',
                'html5'  => true,
                'constraints' => [
                    new Assert\NotBlank(message: "L'heure de livraison est requise."),
                ],
            ])
            ->add('adresse_livraison', TextType::class, [
                'label' => 'Adresse de livraison',
                'attr'  => ['placeholder' => '12 rue de la Paix'],
                'constraints' => [
                    new Assert\NotBlank(message: "L'adresse est requise."),
                    new Assert\Length(max: 255),
                ],
            ])
            ->add('ville_livraison', TextType::class, [
                'label' => 'Ville',
                'attr'  => ['placeholder' => 'Bordeaux'],
                'constraints' => [
                    new Assert\NotBlank(message: 'La ville est requise.'),
                    new Assert\Length(max: 100),
                ],
            ])
            ->add('hors_bordeaux', CheckboxType::class, [
                'label'    => 'Livraison hors Bordeaux (supplement)',
                'required' => false,
                'attr'     => ['id' => 'hors_bordeaux'],
            ])
            ->add('nombre_km', IntegerType::class, [
                'label'    => 'Distance estimee (en km)',
                'required' => false,
                'attr'     => ['min' => 1, 'id' => 'nombre_km'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'menu_preselectionne' => null,
        ]);
    }
}
