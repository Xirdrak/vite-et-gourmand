<?php

namespace App\Form;

use App\Entity\Allergene;
use App\Entity\Plat;
use App\Enum\PlatType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PlatFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titrePlat', TextType::class, [
                'label' => 'Nom du plat',
                'attr'  => ['placeholder' => 'ex: Foie gras mi-cuit'],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nom du plat est obligatoire.'),
                    new Assert\Length(max: 150),
                ],
            ])
            ->add('typePlat', EnumType::class, [
                'class'        => PlatType::class,
                'label'        => 'Type',
                'choice_label' => fn(PlatType $t) => $t->label(),
                'placeholder'  => 'Choisissez un type',
                'constraints'  => [
                    new Assert\NotNull(message: 'Le type est obligatoire.'),
                ],
            ])
            ->add('allergenes', EntityType::class, [
                'class'        => Allergene::class,
                'choice_label' => 'libelle',
                'label'        => 'Allergènes',
                'multiple'     => true,
                'expanded'     => true,
                'required'     => false,
            ])
            ->add('photo', FileType::class, [
                'label'    => 'Photo du plat',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['accept' => 'image/*'],
                'constraints' => [
                    new Assert\File(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Formats acceptés : JPG, PNG, WebP.',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plat::class,
        ]);
    }
}
