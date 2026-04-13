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
use App\Form\CommandeType;
class CommandeController extends AbstractController
{
    #[Route('/commande/liste', name: 'app_commande_liste')]
    public function liste(EntityManagerInterface $em, PaginatorInterface $paginator,Request $request): Response
    {
        // On récupère tous les articles disponibles en base de données
        $query = $em->createQuery('SELECT commande FROM App\Entity\Commande commande');

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
    public function index(EntityManagerInterface $em, Request $request, int $id, PaginatorInterface $paginator): Response
    {
        // On récupère l'commande qui correspond à l'id passé dans l'url
        $commande = $em->getRepository(Commande::class)->findOneBy(['numero_commande' => $id]);

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
            'pagination' => $pagination
        ]);
    }

    #[Route('/commande/add', name: 'app_commande_add')]
    public function add(EntityManagerInterface $em, Request $request): Response
    {
        $mode       = 'new';
        $commande    = new Commande();

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $commande->setStatut('En cours');

            $commande->setUtilisateurId($this->getUser());

            $this->savecommande($commande, $mode, $em);

            return $this->redirectToRoute('app_commande_liste');
        }

        $parameters = array(
            'form'      => $form->createView(),
            'commande'      => $commande,
            'mode'      => $mode
        );

        return $this->render('commande/edit.html.twig', $parameters);
    }

    #[Route('/commande/edit/{id}', name: 'app_commande_edit')]
    public function edit(EntityManagerInterface $em, Request $request, int $id=null): Response
    {
        $mode = 'update';
        // On récupère l'commande qui correspond à l'id passé dans l'url
        $commande = $em->getRepository(Commande::class)->findOneBy(['numero_commande' => $id]);

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {            
            $this->savecommande($commande, $mode, $em);

            return $this->redirectToRoute('app_commande_liste');
        }

        $parameters = array(
            'form'      => $form->createView(),
            'commande'      => $commande,
            'mode'      => $mode
        );

        return $this->render('commande/edit.html.twig', $parameters);
    }

    #[Route('/commande/remove/{id}', name: 'app_commande_remove')]
    public function remove(EntityManagerInterface $em, int $id): Response
    {
        // On récupère l'commande qui correspond à l'id passé dans l'URL
        $commande = $em->getRepository(Commande::class)->findOneBy(['numero_commande' => $id]);

        // L'commande est supprimé
        $em->remove($commande);
        $em->flush();
        
        $this->addFlash('success', 'Le commande a été supprimé avec succès');

        return $this->redirectToRoute('app_commande_liste');
    }

    #[Route('/commande/menuadd/{idcommande}/{idmenu}', name: 'app_commande_menuadd')]
    public function menuadd(EntityManagerInterface $em, int $idcommande, int $idmenu): Response
    {
        // On récupère l'commande qui correspond à l'id passé dans l'url
        $commande = $em->getRepository(commande::class)->findOneBy(['numero_commande' => $idcommande]);

        $menu=  $em->getRepository(Menu::class)->findOneBy(['menu_id' => $idmenu]);

        $commande->setMenuId($menu);
        $em->persist($commande);
        $em->flush();

        $this->addFlash('success', 'Le menu a été ajouté avec succès');

        return $this->redirectToRoute('app_commande_index', ['id' => $idcommande]);
    }   
    
    #[Route('/commande/menuremove/{idcommande}/{idmenu}', name: 'app_commande_menuremove')]
    public function menuremove(EntityManagerInterface $em, int $idcommande, int $idmenu): Response
    {
        // On récupère l'commande qui correspond à l'id passé dans l'url
        $commande = $em->getRepository(commande::class)->findOneBy(['numero_commande' => $idcommande]);

        $menu=  $commande->getMenuId();

        if ($menu) {
            $commande->setMenuId(null);
            $em->persist($commande);
            $em->flush();
        }

        $this->addFlash('success', 'Le menu a été supprimé avec succès');

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
            $this->addFlash('success', 'commande créé avec succès');
        } else {
            $this->addFlash('success', 'commande mis à jour avec succès');
        }
    }    
}

