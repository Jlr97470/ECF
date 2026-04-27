<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\Utilisateur;
use App\Form\CommandeType;

class CommandeController extends AbstractController
{
    #[Route('/commande/liste', name: 'app_commande_liste')]
    public function liste(EntityManagerInterface $em, PaginatorInterface $paginator,Request $request): Response
    {
        // On récupère tous les articles disponibles en base de données
        $user = $this->getUser();
        
        if ($user && (in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_USE', $user->getRoles())))
        {
            // On récupère tous les articles disponibles en base de données
            $queryBuilder = $em->createQueryBuilder()
                ->select('menu')
                ->from(Commande::class, 'commande');            
        }
        else
        {
            // On récupère tous les articles disponibles en base de données
            $queryBuilder = $em->createQueryBuilder()
                ->select('commande')
                ->from(Commande::class, 'commande')
                ->where('commande.utilisateur_id IN (:user)')
                ->setParameter('user', $user);
        }

        $query = $queryBuilder->getQuery();

        $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /* page number */
        10 /* limit per page */
        );      

        return $this->render('commande/liste.html.twig', [
            'pagination' => $pagination
        ]);
    }    

    #[Route('/commande/index/{id}', name: 'app_commande_index')]
    public function index(EntityManagerInterface $em, Request $request, string $id, PaginatorInterface $paginator): Response
    {
        // On récupère l'commande qui correspond à l'id passé dans l'url
        $commande = $em->getRepository(Commande::class)->findOneBy(['numero_commande' => $id]);

        if (!$commande) {
            $this->addFlash('error', 'Le commande n\'existe pas');

            return $this->redirectToRoute('app_commande_liste');
        }

        $menu = $commande->getMenuId();

        if ($menu){
            // On récupère tous les articles disponibles en base de données
            $queryBuilder = $em->createQueryBuilder()
                ->select('menu')
                ->from(Menu::class, 'menu')
                ->where('menu.menu_id NOT IN (:menu)')
                ->setParameter('menu', $menu);
        }
        else
        {
            // On récupère tous les articles disponibles en base de données
            $queryBuilder = $em->createQueryBuilder()
                ->select('menu')
                ->from(Menu::class, 'menu');
        }

        $query = $queryBuilder->getQuery();

        $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /* page number */
        10 /* limit per page */
        );           

        return $this->render('commande/index.html.twig', [
            'commande' => $commande,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/commande/add/{idmenu}', name: 'app_commande_add')]
    public function add(EntityManagerInterface $em, Request $request, ?int $idmenu): Response
    {
        $mode       = 'edit';
        $commande    = new Commande();

        $menu = $em->getRepository(Menu::class)->findOneBy(['menu_id' => $idmenu]);

        if ($menu){
            $commande->setMenuId($menu);
        }

        $form = $this->createForm(CommandeType::class, $commande, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $commande->setNumeroCommande(uniqid('CMD-'));
            $commande->setDateCommande(new \DateTime());
            $commande->setStatut('En cours');
            $commande->setPretmateriel(false);
            $commande->setRestitutionmateriel(false);

            $user = $this->getUser();
        
            if ($user && (in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_USE', $user->getRoles())))
            {
            }
            else
            {
                $commande->setUtilisateurId($user);
            }

            if ($menu){

                if ($menu->getNombrePersonneMinimum() > $commande->getNombrepersonne()) {
                    $this->addFlash('error', 'Le nombre de personne doit être supérieur ou égal à '.$menu->getNombrePersonneMinimum());
                    return $this->redirectToRoute('app_commande_add', ['idmenu' => $idmenu]);
                }

                if ($menu->getQuantiteRestante() < $commande->getNombrepersonne()) {
                    $this->addFlash('error', 'Le nombre de personne doit être inférieur ou égal à '.$menu->getQuantiteRestante());
                    return $this->redirectToRoute('app_commande_add', ['idmenu' => $idmenu]);
                }

                $commande->setPrixMenu($menu->getPrixParPersonne()*$commande->getNombrePersonne());
                $commande->setPrixlivraison($menu->getPrixParPersonne() *$commande->getNombrePersonne() * 0.1);  

                $menu = $commande->getMenuId();
                $menu->setQuantiteRestante($menu->getQuantiteRestante() - $commande->getNombrepersonne());
                $em->persist($menu);
            }

            $this->savecommande($commande, $mode, $em);

            return $this->redirectToRoute('app_commande_index', ['id' => $commande->getNumeroCommande()]);
        }

        $parameters = array(
            'form'      => $form->createView(),
            'commande'      => $commande,
            'mode'      => $mode
        );

        return $this->render('commande/edit.html.twig', $parameters);
    }

    #[Route('/commande/edit/{id}', name: 'app_commande_edit')]
    public function edit(EntityManagerInterface $em, Request $request, string $id): Response
    {
        $mode = 'update';
        // On récupère l'commande qui correspond à l'id passé dans l'url
        $commande = $em->getRepository(Commande::class)->findOneBy(['numero_commande' => $id]);

        if (!$commande) {
            $this->addFlash('error', 'Le commande n\'existe pas');

            return $this->redirectToRoute('app_commande_liste');
        }

        $user = $this->getUser();

        if ($user && (in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_USE', $user->getRoles())))
        {
        }
        else
        {
            if ($commande->getUtilisateurId() && $commande->getUtilisateurId()->getUtilisateurId() !== $this->getUser()->getUtilisateurId()) {
                $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cette commande');
                return $this->redirectToRoute('app_commande_index', ['id' => $id]);
            }

            if ($commande->getStatut() <> 'En cours') {
                $this->addFlash('error', 'Le commande ne peut pas être modifié car il déjà en cours de livraison ou livré');   
                return $this->redirectToRoute('app_commande_index', ['id' => $id]);
            }
        }

        $form = $this->createForm(CommandeType::class, $commande, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {  

            if ($commande->getMenuId()->getNombrePersonneMinimum() > $commande->getNombrepersonne()) {
                $this->addFlash('error', 'Le nombre de personne doit être supérieur ou égal à '.$commande->getMenuId()->getNombrePersonneMinimum());
                return $this->redirectToRoute('app_commande_edit', ['id' => $id]);
            }
            
            if ($commande->getMenuId()->getQuantiteRestante() < $commande->getNombrepersonne()) {
                $this->addFlash('error', 'Le nombre de personne doit être inférieur ou égal à '.$commande->getMenuId()->getQuantiteRestante());
                return $this->redirectToRoute('app_commande_edit', ['id' => $id]);
            }

            $commande->setPrixMenu($commande->getMenuId()->getPrixParPersonne()*$commande->getNombrePersonne());
            $commande->setPrixlivraison($commande->getMenuId()->getPrixParPersonne() *$commande->getNombrePersonne() * 0.1);  

            $this->savecommande($commande, $mode, $em);

            return $this->redirectToRoute('app_commande_index', ['id' => $id]);
        }

        $parameters = array(
            'form'      => $form->createView(),
            'commande'      => $commande,
            'mode'      => $mode
        );

        return $this->render('commande/edit.html.twig', $parameters);
    }

    #[Route('/commande/remove/{id}', name: 'app_commande_remove')]
    public function remove(EntityManagerInterface $em, string $id): Response
    {
        // On récupère l'commande qui correspond à l'id passé dans l'URL
        $commande = $em->getRepository(Commande::class)->findOneBy(['numero_commande' => $id]);

        if (!$commande) {
            $this->addFlash('error', 'Le commande n\'existe pas');

            return $this->redirectToRoute('app_commande_liste');
        }

        if ($commande->getStatut() <> 'En cours') {
            $this->addFlash('error', 'Le commande ne peut pas être supprimé car il déjà en cours de livraison ou livré');   
            return $this->redirectToRoute('app_commande_index', ['id' => $id]);
        }

        if ($commande->getMenuId()) {
            $menu = $commande->getMenuId();
            $menu->setQuantiteRestante($menu->getQuantiteRestante() + $commande->getNombrepersonne());
            $em->persist($menu);
        }

        // L'commande est supprimé
        $em->remove($commande);
        $em->flush();
        
        $this->addFlash('success', 'Le commande a été supprimé avec succès');

        return $this->redirectToRoute('app_commande_liste');
    }

    #[Route('/commande/menuadd/{idcommande}/{idmenu}', name: 'app_commande_menuadd')]
    public function menuadd(EntityManagerInterface $em, string $idcommande, int $idmenu): Response
    {
        // On récupère l'commande qui correspond à l'id passé dans l'url
        $commande = $em->getRepository(commande::class)->findOneBy(['numero_commande' => $idcommande]);

        $menu=  $em->getRepository(Menu::class)->findOneBy(['menu_id' => $idmenu]);

        if (!$commande) {
            $this->addFlash('error', 'Le commande n\'existe pas');

            return $this->redirectToRoute('app_commande_liste');
        }

        if (!$menu) {
            $this->addFlash('error', 'Le menu n\'existe pas');      
            return $this->redirectToRoute('app_commande_index', ['id' => $idcommande]);
        }

        if ($commande->getMenuId()) {
            $menu_old = $commande->getMenuId();
            $menu_old->setQuantiteRestante($menu_old->getQuantiteRestante() + $commande->getNombrepersonne());
            $em->persist($menu_old);
        }        

        if ($menu->getNombrePersonneMinimum() > $commande->getNombrepersonne()) {
            $this->addFlash('error', 'Le nombre de personne doit être supérieur ou égal à '.$menu->getNombrePersonneMinimum());
            return $this->redirectToRoute('app_commande_edit', ['id' => $idcommande]);
        }
        
        if ($menu->getQuantiteRestante() < $commande->getNombrepersonne()) {
            $this->addFlash('error', 'Le nombre de personne doit être inférieur ou égal à '.$menu->getQuantiteRestante());
            return $this->redirectToRoute('app_commande_edit', ['id' => $idcommande]);
        }        

        $commande->setPrixMenu($menu->getPrixParPersonne()*$commande->getNombrepersonne());
        $commande->setPrixlivraison($menu->getPrixParPersonne() *$commande->getNombrepersonne() * 0.1);  
        $menu->setQuantiteRestante($menu->getQuantiteRestante() - $commande->getNombrepersonne());

        $commande->setMenuId($menu);
        $em->persist($commande);
        $em->persist($menu);
        $em->flush();

        $this->addFlash('success', 'Le menu a été ajouté avec succès');

        return $this->redirectToRoute('app_commande_index', ['id' => $idcommande]);
    }    
 
    #[Route('/commande/utilisateuradd/{idcommande}/{idutilisateur}', name: 'app_commande_utilisateuradd')]
    public function utilisateuradd(EntityManagerInterface $em, string $idcommande, int $idutilisateur): Response
    {
        // On récupère l'commande qui correspond à l'id passé dans l'url
        $commande = $em->getRepository(commande::class)->findOneBy(['numero_commande' => $idcommande]);

        $user=  $em->getRepository(Utilisateur::class)->findOneBy(['utilisateur_id' => $idutilisateur]);

        if (!$commande) {
            $this->addFlash('error', 'Le commande n\'existe pas');

            return $this->redirectToRoute('app_commande_liste');
        }

        if (!$user) {
            $this->addFlash('error', "L'Utilisateur n'existe pas");
            return $this->redirectToRoute('app_commande_index', ['id' => $idcommande]);
        }       

        $commande->setUtilisateurId($user);
        $em->persist($commande);
        $em->flush();

        $this->addFlash('success', 'L\'Utilisateur a été ajouté avec succès');

        return $this->redirectToRoute('app_commande_index', ['id' => $idcommande]);
    }    
    
    /**
     * Enregistrer un commande en base de données
     * 
     * @param   commande     $commande
     * @param   string      $mode 
     */
    private function savecommande(commande $commande, string $mode, EntityManagerInterface $em): void{
        $em->persist($commande);
        $em->flush();
        
        if($mode == 'new') {
            $this->addFlash('success', 'La commande créé avec succès');
        } else {
            $this->addFlash('success', 'La commande mis à jour avec succès');
        }
    }    
}

