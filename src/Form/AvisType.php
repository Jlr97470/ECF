<?php
 
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

use App\Entity\Avis;
class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Titre
        $builder->add('note', TextType::class, [
            'label' => 'Note de l\'Avis',
            'attr' => ['placeholder' => 'Note de l\'Avis'],
            'required' => true,
            'constraints' => [
            new NotBlank([
                'message' => 'Ce champ ne peut être vide',
            ]),
            new Length([
                // max length allowed by Symfony for security reasons
                'max' => 50,
                'maxMessage' => 'Votre avis ne peut pas avoir plus de {{ limit }} caractères',
            ]),
    ]
        ])
        ->add('description', TextType::class, [
            'label' => 'Description de l\'Avis',   
            'attr' => ['placeholder' => 'Description de l\'Avis'],
            'required' => true,
            'constraints' => [
            new NotBlank([
                'message' => 'Ce champ ne peut être vide',
            ]),
            new Length([
                // max length allowed by Symfony for security reasons
                'max' => 50,
                'maxMessage' => 'Votre description ne peut pas avoir plus de {{ limit }} caractères',
            ]),
            ]
        ])
        ->add('statut', ChoiceType::class, [
            'label' => 'Statut de l\'Avis',
            'attr' => ['placeholder' => 'Statut de l\'Avis'],
            'required' => true,
            'choices' => [
                'En Attente' => Null,
                'Validé' => 'Validé'
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
            'data_class' => Avis::class,
        ]);
    }

}
