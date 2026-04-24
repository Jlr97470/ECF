<?php
 
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;


use App\Entity\Horaire;
class HoraireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Titre
        $builder->add('jour', TextType::class, [
            'label' => 'Jour de l\'Horaire',   
            'attr' => ['placeholder' => 'Jour de l\'Horaire'],
            'required' => true,
            'constraints' => [
            new NotBlank([
                'message' => 'Ce champ ne peut être vide',
            ]),
            new Length([
                // max length allowed by Symfony for security reasons
                'max' => 50,
                'maxMessage' => 'Votre libellé ne peut pas avoir plus de {{ limit }} caractères',
            ]),
            ]
        ])
        ->add('heureouverture', TextType::class, [
            'label' => 'Heure d\'ouverture',   
            'attr' => ['placeholder' => 'Heure d\'ouverture'],
            'required' => true,
            'constraints' => [
            new NotBlank([
                'message' => 'Ce champ ne peut être vide',
            ]),
            new Length([
                // max length allowed by Symfony for security reasons
                'max' => 50,
                'maxMessage' => 'Votre libellé ne peut pas avoir plus de {{ limit }} caractères',
            ]),
            new Regex([
                'message' => 'Veuillez entrer une heure au format HH:MM',
                'pattern' => '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/',
            ]),
            ]
        ])
        ->add('heurefermeture', TextType::class, [
            'label' => 'Heure de fermeture',   
            'attr' => ['placeholder' => 'Heure de fermeture'],
            'required' => true,
            'constraints' => [
            new NotBlank([
                'message' => 'Ce champ ne peut être vide',
            ]),
            new Length([
                // max length allowed by Symfony for security reasons
                'max' => 50,
                'maxMessage' => 'Votre libellé ne peut pas avoir plus de {{ limit }} caractères',
            ]),
            new Regex([
                'message' => 'Veuillez entrer une heure au format HH:MM',
                'pattern' => '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/',
            ]),
            ]
        ]);
        // Bouton Envoyer
        $builder->add('submit', SubmitType::class, array(
            'label' => 'Enregistrer'
        ));        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Horaire::class,
        ]);
    }

}
