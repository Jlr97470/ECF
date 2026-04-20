<?php
 
namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class CommandeType extends AbstractType
{
    public function addNumeroCommandeField(FormBuilderInterface $builder)
    {
        // Numero de commande
        $builder->add('numerocommande', TextType::class, [
            'label' => 'Numéro de commande',
            'attr' => ['placeholder' => 'Numéro de commande'],
            'required' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'Ce champ ne peut être vide'
                ]), new Length([
                    // max length allowed by Symfony for security reasons
                    'max' => 50,
                ])
            ]
        ]);
    }    

    public function addDateCommandeField(FormBuilderInterface $builder)
    {
        // Date de la commande
        $builder->add('datecommande', DateType::class, [
            'label' => 'Date de la commande',
            'attr' => ['placeholder' => 'Date de la commande'],
            'required' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'Ce champ ne peut être vide'
                ])
            ]
        ]);
    }    

    public function addHeureLivraisonField(FormBuilderInterface $builder)
    {
        // Heure de livraison
        $builder->add('heurelivraison', TextType::class, [
            'label' => 'Heure de livraison',
            'attr' => ['placeholder' => 'Heure de livraison'],      
            'required' => false,
            'constraints' => [
                new NotBlank([
                    'message' => 'Ce champ ne peut être vide'
                ]), new Length([        
                    // max length allowed by Symfony for security reasons
                    'max' => 50,
                ])
            ]
        ]);
    }  
    
    public function addPrixMenuField(FormBuilderInterface $builder)
    {
        // Prix menu      
        $builder->add('prixmenu', MoneyType::class, [
            'label' => 'Prix du Menu',
            'attr' => ['placeholder' => 'Prix du Menu'],
            'required' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'Ce champ ne peut être vide'
                ]),
                new GreaterThan([
                    'value' => 0,
                    'message' => 'Le prix du Menu doit être supérieur à 0'
                ])
            ]
        ]);
    }   
    
    public function addPrixLivraisonField(FormBuilderInterface $builder)
    {
        // Prix de livraison
        $builder->add('prixlivraison', MoneyType::class, [
            'label' => 'Prix de livraison',
            'attr' => ['placeholder' => 'Prix de livraison'],       
            'required' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'Ce champ ne peut être vide'
                ]), new GreaterThan([   
                    'value' => 0,
                    'message' => 'Le prix de livraison doit être supérieur à 0'
                ])
            ]
        ]);
    }   
    
    public function addStatutField(FormBuilderInterface $builder)
    {
        $builder->add('statut', ChoiceType::class, [
            'label' => 'Statut',
            'attr' => ['placeholder' => 'Statut'],
            'required' => true,
            'choices' => [
                'En Cours' => 'En Cours',
                'Accepté' => 'Accepté',
                'En préparation' => 'En préparation',
                'En cours de livraison' => 'En cours de livraison',
                'Livré' => 'Livré',
                'En attente du retour de matériel' => 'En attente du retour de matériel',
                'Terminé' => 'Terminé',
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Ce champ ne peut être vide'
                ]),
            ],
        ]); 
    }  
    
    public function addRestitutionMaterielField(FormBuilderInterface $builder)
    {
        // Restitution matériel
        $builder->add('restitutionmateriel', CheckboxType::class, [
            'label' => 'Restitution du matériel',
            'required' => false,        
        ]);
    }     
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'] ?? null;

        if ($user && (in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_USE', $user->getRoles()))) {
            $this->addNumeroCommandeField($builder);
            $this->addDateCommandeField($builder);
            $this->addHeureLivraisonField($builder);
            $this->addPrixMenuField($builder);
            $this->addPrixLivraisonField($builder);
            $this->addStatutField($builder);
            $this->addRestitutionMaterielField($builder);
        }        

        // Date de la prestation
        $builder->add('dateprestation', DateType::class, [
            'label' => 'Date de la prestation',
            'attr' => ['placeholder' => 'Date de la prestation'],
            'required' => false,
            'constraints' => [
                new NotBlank([      
                    'message' => 'Ce champ ne peut être vide'
                ])
            ]
        ]);

        // Nombre de personnes
        $builder->add('nombrepersonne', NumberType::class, [      
            'label' => 'Nombre de personnes',
            'attr' => ['placeholder' => 'Nombre de personnes'],
            'required' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'Ce champ ne peut être vide'
                ]), new GreaterThan([
                    'value' => 0,
                    'message' => 'Le nombre de personnes doit être supérieur à 0'
                ])
            ]
        ]);       

        // Pret matériel
        $builder->add('pretmateriel', CheckboxType::class, [
            'label' => 'Prêt de matériel',
            'required' => true,        
        ]);

        // Bouton Envoyer
        $builder->add('submit', SubmitType::class, array(
            'label' => 'Enregistrer'
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
            'user' => null,
        ]);
    }

}
