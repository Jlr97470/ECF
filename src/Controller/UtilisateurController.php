<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Utilisateur;
use App\Entity\Possede;
use App\Form\UtilisateurType;
use App\Repository\RoleRepository;

class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur/liste', name: 'app_utilisateur_liste')]
    public function liste(EntityManagerInterface $em, PaginatorInterface $paginator,Request $request): Response
    {
        // On récupère tous les articles disponibles en base de données
        $query = $em->createQuery('SELECT utilisateur FROM App\Entity\Utilisateur utilisateur');

        $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /* page number */
        10 /* limit per page */
        );          

        return $this->render('utilisateur/liste.html.twig', [
            'pagination' => $pagination
        ]);
    }    

    #[Route('/utilisateur/index/{id}', name: 'app_utilisateur_index')]
    public function index(EntityManagerInterface $em,int $id): Response
    {
        // On récupère l'utilisateur qui correspond à l'id passé dans l'url
        $utilisateur = $em->getRepository(Utilisateur::class)->findOneBy(['utilisateur_id' => $id]);

        return $this->render('utilisateur/index.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/utilisateur/add', name: 'app_utilisateur_add')]
    public function add(EntityManagerInterface $em, Request $request, RoleRepository $roleRepository, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
    {
        $mode       = 'new';
        $utilisateur    = new Utilisateur();

        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $errors = $validator->validate($utilisateur);

            if (count($errors) === 0) {

                $utilisateur->setPassword(
                    $passwordHasher->hashPassword(
                        $utilisateur,
                        $form->get('password')->getData()
                    )
                );
                $roleData = $form->get('roles')->getData()[0];
                
                $role = $roleRepository->findOneBy(['libelle' => $roleData]);

                if (!$role) {
                        throw new \Exception("Role avec libelle '" . $roleData . "' not found");
                }

                $possede=new Possede();
                    
                $possede->setRoleId($role);
                $possede->setUtilisateurId($utilisateur);
                $utilisateur->addPossede($possede);
                
                $this->saveUtilisateur($utilisateur, $mode,$em);

                return $this->redirectToRoute('app_utilisateur_liste');
            }
        }

        $parameters = array(
            'form'      => $form->createView(),
            'utilisateur'      => $utilisateur,
            'mode'      => $mode
        );

        return $this->render('utilisateur/edit.html.twig', $parameters);
    }

    #[Route('/utilisateur/edit/{id}', name: 'app_utilisateur_edit')]
    public function edit(EntityManagerInterface $em, Request $request, int $id=null, RoleRepository $roleRepository, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
    {
        $mode = 'update';
        // On récupère l'utilisateur qui correspond à l'id passé dans l'url
        $utilisateur = $em->getRepository(Utilisateur::class)->findOneBy(['utilisateur_id' => $id]);

        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) { 

            $errors = $validator->validate($utilisateur);

            if (count($errors) === 0) {        
                $utilisateur->setPassword(
                    $passwordHasher->hashPassword(
                        $utilisateur,
                        $form->get('password')->getData()
                    )
                );   
                
                $utilisateur->removePossede($utilisateur->getPossede());

                $em->flush();

                $roleData = $form->get('roles')->getData()[0];

                $role = $roleRepository->findOneBy(['libelle' => $roleData]);

                if (!$role) {
                    throw new \Exception("Role avec libelle '" . $roleData . "' not found");
                }
                
                $possede=new Possede();                

                $possede->setRoleId($role);
                $possede->setUtilisateurId($utilisateur);
                $utilisateur->addPossede($possede);
                                
                $this->saveUtilisateur($utilisateur, $mode,$em);

                return $this->redirectToRoute('app_utilisateur_liste');
            }
        }

        $parameters = array(
            'form'      => $form->createView(),
            'utilisateur'      => $utilisateur,
            'mode'      => $mode
        );

        return $this->render('utilisateur/edit.html.twig', $parameters);
    }

    #[Route('/utilisateur/remove/{id}', name: 'app_utilisateur_remove')]
    public function remove(EntityManagerInterface $em, int $id): Response
    {
        // On récupère l'utilisateur qui correspond à l'id passé dans l'URL
        $utilisateur = $em->getRepository(Utilisateur::class)->findBy(['utilisateur_id' => $id])[0];

        // L'utilisateur est supprimé
        $em->remove($utilisateur);
        $em->flush();
        
        $this->addFlash('success', 'L\'utilisateur a été supprimé avec succès');

        return $this->redirectToRoute('app_utilisateur_liste');
    }

    /**
     * Enregistrer un utilisateur en base de données
     * 
     * @param   utilisateur     $utilisateur
     * @param   string      $mode 
     */
    private function saveUtilisateur(Utilisateur $utilisateur, string $mode, EntityManagerInterface $em){
        $em->persist($utilisateur);
        $em->flush();
        if($mode == 'new') {
            $this->addFlash('success', 'Utilisateur créé avec succès');
        } else {
            $this->addFlash('success', 'Utilisateur mis à jour avec succès');
        }
    }    
}
