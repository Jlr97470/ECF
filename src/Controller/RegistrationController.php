<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

use App\Entity\Utilisateur;
use App\Entity\Possede;
use App\Entity\Role;
use App\Form\RegistrationType;


class RegistrationController extends AbstractController
{

    #[Route("/register", "app_registration_register")]  
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em, ValidatorInterface $validator, MailerInterface $mailer): Response
    {
        $utilisateur = new Utilisateur();
        $possede=new Possede();
        $role = $em->getRepository(Role::class)->findOneBy(['libelle' => 'ROLE_USER']);
        if (!$role) {
            throw new \Exception("Role with libelle 'ROLE_USER' not found");
        }
        $possede->setRoleId($role);
        $possede->setUtilisateurId($utilisateur);
        $utilisateur->addPossede($possede);
        $form = $this->createForm(RegistrationType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur->setEmail($form->get('email')->getData());
            // encode the plain password
            $utilisateur->setPassword(
                $passwordHasher->hashPassword(
                    $utilisateur,
                    $form->get('password')->getData()
                )
            );
            $utilisateur->setPrenom($form->get('prenom')->getData());
            $utilisateur->setNom($form->get('nom')->getData());
            $utilisateur->setTelephone($form->get('telephone')->getData());
            $utilisateur->setVille($form->get('ville')->getData());
            $utilisateur->setPays($form->get('pays')->getData());
            $utilisateur->setAdressePostal($form->get('adresse_postal')->getData());

            $errors = $validator->validate($utilisateur);

            if (count($errors) === 0) {

                if ($form->isSubmitted() && $form->isValid()) {
                
                    $em->persist($utilisateur);
                    $em->flush();
                // do anything else you need here, like send an email
                    $role = $em->getRepository(Role::class)->findOneBy(['libelle' => 'ROLE_ADMIN']);

                    $possedes= $role->getPossedes();

                    $email='';

                    foreach ($possedes as $possede) {    

                        $email.= $possede->getUtilisateurId()->getEmail().',';

                    }   

                    $email = rtrim($email, ',');

                    $message = (new TemplatedEmail())
                        ->from($email)
                        ->to($utilisateur->getEmail())
                        ->subject('Bienvenue sur notre site Vite & Gourmand')
                        ->htmlTemplate('emails/bienvenue.html.twig')
                        ->context([
                            'prenom' => $utilisateur->getPrenom(),
                            'nom' => $utilisateur->getNom(),
                        ]);

                    try {
                        $mailer->send($message);
                    } catch (TransportExceptionInterface $e) {
                        // some error prevented the email sending; display an
                        // error message or try to resend the message
                        $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer plus tard.');
                    }

                    $this->addFlash('success', 'Votre compte a été créé avec succès !');
                    
                    return $this->redirectToRoute('homepage');
                }
                else {
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                    return $this->redirectToRoute('app_registration_register');
                }   
            }
        }

        // Always return a Response, even if form is not submitted or not valid
        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
