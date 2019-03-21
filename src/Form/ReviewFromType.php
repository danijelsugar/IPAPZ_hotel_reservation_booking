<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewFromType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add(
                'text',
                TextareaType::class,
                [
                    'label' => 'Text',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add(
                'rating',
                NumberType::class,
                [
                    'label' => 'Ocijena',
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
                'data_class' => Review::class
            ]
        );
    }
}
