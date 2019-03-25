<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Room;
use App\Entity\SubCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'description',
                TextareaType::class,
                [
                    'label' => 'Opis',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add(
                'category',
                EntityType::class,
                [
                    'label' => 'Kategorija',
                    'class' => Category::class,
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'choice_label' => 'name'
                ]
            )
            ->add(
                'subcategory',
                EntityType::class,
                [
                    'label' => 'Potkategorija',
                    'class' => SubCategory::class,
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'choice_label' => 'name'
                ]
            )
            ->add(
                'image',
                FileType::class,
                [
                    'data_class' => null,
                    'label' => 'Slika(JPG, JPEG)',
                ]
            )
            ->add(
                'capacity',
                NumberType::class,
                [
                    'label' => 'Kapacitet sobe',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )->add(
                'cost',
                MoneyType::class,
                [
                    'label' => 'Cijena sobe po danu',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Room::class
            ]
        );
    }
}
