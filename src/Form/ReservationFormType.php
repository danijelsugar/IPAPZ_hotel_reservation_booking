<?php


namespace App\Form;


use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'datefrom', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker'],
                'html5' => false,
                'format' => 'dd.MM.yyyy.'
                ]
            )
            ->add(
                'dateto', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker'],
                'html5' => false,
                'format' => 'dd.MM.yyyy.'
                ]
            )
            ->add(
                'personNum', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3
                ],
                'label' => 'Broj osoba',
                'expanded' => true,

                ]
            );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
            'data_class' => Reservation::class
            ]
        );
    }
}