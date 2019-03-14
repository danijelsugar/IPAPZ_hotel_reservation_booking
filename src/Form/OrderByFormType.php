<?php
/**
 * Created by PhpStorm.
 * User: polaznik07
 * Date: 3/13/19
 * Time: 8:29 AM
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderByFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('orderby', ChoiceType::class, [
           'choices' => [
               'Datum' => 1,
               'Email' => 2,
               'Naziv sobe' => 3
           ],
           'label' => 'Sortiraj po',
            'expanded' => true,
            'placeholder' => 'Sortiraj'

        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('required', false);
    }

}