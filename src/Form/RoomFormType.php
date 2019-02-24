<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Room;
use App\Entity\SubCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextareaType::class, [
               'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('category', EntityType::class, [
               'class' => Category::class,
                'attr' => [
                    'class' => 'form-control'
                ],
               'choice_label' => 'name'
            ])
            ->add('subcategory', EntityType::class, [
               'class' => SubCategory::class,
                'attr' => [
                    'class' => 'form-control'
                ],
                'choice_label' => 'name'
            ])
            ->add('amount', IntegerType::class, [
                'label' => 'Amount',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('image', FileType::class, [
               'label' => 'Image(JPG, JPEG)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Room::class
        ]);
    }
}