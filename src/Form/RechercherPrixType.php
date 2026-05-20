<?php

namespace App\Form;

use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

use App\Entity\RechercherPrix;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class RechercherPrixType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prix_min', MoneyType::class, [
                'label' => 'Prix minimum',
                'required' => false,
                'scale' => 2,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut être vide',
                    ]),
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'Le prix doit être supérieur à zéro',
                    ]),
                ],
            ])
            ->add('prix_max', MoneyType::class, [
                'label' => 'Prix maximum',
                'required' => false,
                'scale' => 2,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut être vide',
                    ]),
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'Le prix doit être supérieur à zéro',
                    ]),
                ],
            ]); 

        $builder->add('submit', SubmitType::class, array(
            'label' => 'Filter'
        ));              
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RechercherPrix::class,
        ]);
    }
}   
