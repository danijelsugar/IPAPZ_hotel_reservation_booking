<?php


namespace App\Form;


use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Ime',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Prezime',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'label' => 'Lozinka',
                'attr' => [
                    'class' => 'form-control'
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Unesite lozinku'
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Vaša bi lozinka trebala biti najmanje {{ limit }} znakova',
                        'max' => 4096,
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}