<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\Plat;
use App\Entity\Regime;
use App\Entity\Theme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du menu',
                'attr'  => ['placeholder' => 'ex: Menu de Noel Tradition'],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le titre est obligatoire.'),
                    new Assert\Length(max: 150),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr'  => ['rows' => 5, 'placeholder' => 'Presentation du menu...'],
                'constraints' => [
                    new Assert\NotBlank(message: 'La description est obligatoire.'),
                ],
            ])
            ->add('theme', EntityType::class, [
                'class'        => Theme::class,
                'choice_label' => 'libelle',
                'label'        => 'Thème',
                'placeholder'  => 'Choisissez un thème',
                'constraints'  => [
                    new Assert\NotNull(message: 'Le thème est obligatoire.'),
                ],
            ])
            ->add('regime', EntityType::class, [
                'class'        => Regime::class,
                'choice_label' => 'libelle',
                'label'        => 'Régime',
                'placeholder'  => 'Choisissez un régime',
                'constraints'  => [
                    new Assert\NotNull(message: 'Le régime est obligatoire.'),
                ],
            ])
            ->add('nombrePersonneMinimum', IntegerType::class, [
                'label' => 'Nombre de personnes minimum',
                'attr'  => ['min' => 1],
                'constraints' => [
                    new Assert\NotBlank(message: 'Ce champ est obligatoire.'),
                    new Assert\Positive(message: 'La valeur doit etre positive.'),
                ],
            ])
            ->add('prixParPersonne', NumberType::class, [
                'label'  => 'Prix par personne (€)',
                'scale'  => 2,
                'attr'   => ['min' => 0, 'step' => '0.01'],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le prix est obligatoire.'),
                    new Assert\PositiveOrZero(message: 'Le prix ne peut pas etre negatif.'),
                ],
            ])
            ->add('quantiteRestante', IntegerType::class, [
                'label' => 'Stock disponible (nb de commandes)',
                'attr'  => ['min' => 0],
                'constraints' => [
                    new Assert\NotBlank(message: 'Ce champ est obligatoire.'),
                    new Assert\PositiveOrZero(),
                ],
            ])
            ->add('conditions', TextareaType::class, [
                'label'    => 'Conditions (délai, stockage...)',
                'required' => false,
                'attr'     => ['rows' => 3, 'placeholder' => 'ex: Commander 7 jours avant la prestation...'],
            ])
            ->add('actif', CheckboxType::class, [
                'label'    => 'Menu visible et commandable',
                'required' => false,
            ])
            ->add('plats', EntityType::class, [
                'class'        => Plat::class,
                'choice_label' => fn(Plat $p) => $p->getTitrePlat() . ' (' . $p->getTypePlat()->label() . ')',
                'label'        => 'Plats du menu',
                'multiple'     => true,
                'expanded'     => true,
                'required'     => false,
            ])
            ->add('images', FileType::class, [
                'label'    => 'Ajouter des images',
                'mapped'   => false,
                'multiple' => true,
                'required' => false,
                'attr'     => ['accept' => 'image/*'],
                'constraints' => [
                    new Assert\All([
                        'constraints' => [
                            new Assert\File([
                                'maxSize'          => '5M',
                                'mimeTypes'        => ['image/jpeg', 'image/png', 'image/webp'],
                                'mimeTypesMessage' => 'Formats acceptés : JPG, PNG, WebP.',
                            ]),
                        ],
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
