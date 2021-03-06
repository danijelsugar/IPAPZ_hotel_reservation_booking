<?php


namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'datefrom',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'attr' =>
                        [
                            'class' => 'js-datepicker form-control',
                            'autocomplete' => 'off'
                        ],
                    'html5' => false,
                    'format' => 'dd.MM.yyyy.',
                    'constraints' =>
                        [
                            new NotBlank(),
                            new GreaterThanOrEqual('today')
                        ],
                    'invalid_message' => 'Promijeni zavrsni datum',
                    'label' => 'Datum od'

                ]
            )
            ->add(
                'dateto',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'attr' =>
                        [
                            'class' => 'js-datepicker form-control',
                            'autocomplete' => 'off'
                        ],
                    'html5' => false,
                    'format' => 'dd.MM.yyyy.',
                    'constraints' =>
                        [
                            new NotBlank(),
                            new GreaterThan('today')
                        ],
                    'invalid_message' => 'Promijeni zavrsni datum',
                    'label' => 'Datum do'

                ]
            )
            ->add(
                'personNum',
                ChoiceType::class,
                [
                    'mapped' => false,
                    'choices' => [
                        '1' => 1,
                        '2' => 2,
                        '3' => 3
                    ],
                    'label' => 'Broj osoba',
                    'expanded' => true,
                    'constraints' =>
                        [
                            new NotBlank()
                        ]

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
