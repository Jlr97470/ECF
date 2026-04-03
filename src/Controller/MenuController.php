<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Menu;
use App\Entity\Plat;
use App\Entity\ProposePlat;
use App\Form\MenuType;
class MenuController extends AbstractController
{
    #[Route('/menu/liste', name: 'app_menu_liste')]
    public function liste(EntityManagerInterface $em, PaginatorInterface $paginator,Request $request): Response
    {
        // On récupère tous les articles disponibles en base de données
        $query = $em->createQuery('SELECT menu FROM App\Entity\Menu menu');

        $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /* page number */
        10 /* limit per page */
        );      

        return $this->render('menu/liste.html.twig', [
            'pagination' => $pagination
        ]);
    }    

    #[Route('/menu/index/{id}', name: 'app_menu_index')]
    public function index(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request, int $id): Response
    {
        // On récupère l'Menu qui correspond à l'id passé dans l'url
        $menu = $em->getRepository(Menu::class)->findOneBy(['menu_id' => $id]);

        $plats = $menu->getProposePlats()->map(function($proposeplat) {
            return $proposeplat->getPlatId();
        })->toArray();

        $theme = $menu->getProposeTheme()->getThemeId();

        if ($plats){
            // On récupère tous les articles disponibles en base de données
            $queryBuilder = $em->createQueryBuilder()
                ->select('plat')
                ->from(Plat::class, 'plat')
                ->where('plat.plat_id NOT IN (:plats)')
                ->setParameter('plats', $plats);
        }
        else
        {
            // On récupère tous les articles disponibles en base de données
            $queryBuilder = $em->createQueryBuilder()
                ->select('plat')
                ->from(Plat::class, 'plat');
        }

        $query = $queryBuilder->getQuery();

        $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /* page number */
        10 /* limit per page */
        );          

        return $this->render('menu/index.html.twig', [
            'theme' => $theme,
            'menu' => $menu,
            'plats' => $plats,
            'pagination' => $pagination
        ]);
    }

    #[Route('/menu/add', name: 'app_menu_add')]
    public function add(EntityManagerInterface $em, Request $request): Response
    {
        $mode       = 'new';
        $menu    = new Menu();

        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->saveMenu($menu, $mode, $em);

            return $this->redirectToRoute('app_menu_liste');
        }

        $parameters = array(
            'form'      => $form->createView(),
            'menu'      => $menu,
            'mode'      => $mode
        );

        return $this->render('menu/edit.html.twig', $parameters);
    }

    #[Route('/menu/edit/{id}', name: 'app_menu_edit')]
    public function edit(EntityManagerInterface $em, Request $request, int $id=null): Response
    {
        $mode = 'update';
        // On récupère l'Menu qui correspond à l'id passé dans l'url
        $menu = $em->getRepository(Menu::class)->findOneBy(['menu_id' => $id]);

        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {            
            $this->saveMenu($menu, $mode, $em);

            return $this->redirectToRoute('app_menu_liste');
        }

        $parameters = array(
            'form'      => $form->createView(),
            'Menu'      => $menu,
            'mode'      => $mode
        );

        return $this->render('menu/edit.html.twig', $parameters);
    }

    #[Route('/menu/remove/{id}', name: 'app_menu_remove')]
    public function remove(EntityManagerInterface $em, int $id): Response
    {
        // On récupère l'Menu qui correspond à l'id passé dans l'URL
        $menu = $em->getRepository(Menu::class)->findOneBy(['menu_id' => $id]);

        // L'Menu est supprimé
        $em->remove($menu);
        $em->flush();
        
        $this->addFlash('success', 'Le menu a été supprimé avec succès');

        return $this->redirectToRoute('app_menu_liste');
    }

    #[Route('/menu/platadd/{idmenu}/{idplat}', name: 'app_menu_platadd')]
    public function platadd(EntityManagerInterface $em, int $idmenu, int $idplat): Response
    {
        // On récupère l'Menu qui correspond à l'id passé dans l'url
        $menu = $em->getRepository(Menu::class)->findOneBy(['menu_id' => $idmenu]);

        $plat=  $em->getRepository(Plat::class)->findOneBy(['plat_id' => $idplat]);

        $proposeplat = new ProposePlat();
        $proposeplat->setMenuId($menu);
        $proposeplat->setPlatId($plat);
        $em->persist($proposeplat);
        $em->flush();

        $this->addFlash('success', 'Le plat a été ajouté avec succès');

        return $this->redirectToRoute('app_menu_index', ['id' => $idmenu]);
    }   
    
      #[Route('/menu/platremove/{idmenu}/{idplat}', name: 'app_menu_platremove')]
    public function platremove(EntityManagerInterface $em, int $idmenu, int $idplat): Response
    {
        // On récupère l'Menu qui correspond à l'id passé dans l'url
        $menu = $em->getRepository(Menu::class)->findOneBy(['menu_id' => $idmenu]);

        $proposeplat=  $menu->getProposePlats()->filter(function($proposeplat) use ($idplat) {
            return $proposeplat->getPlatId()->getPlatId() === $idplat;
        })->first();

        if ($proposeplat) {
            $em->remove($proposeplat);
            $em->flush();
        }

        $this->addFlash('success', 'Le plat a été supprimé avec succès');

        return $this->redirectToRoute('app_menu_index', ['id' => $idmenu]);
    }     
    /**
     * Enregistrer un Menu en base de données
     * 
     * @param   Menu     $Menu
     * @param   string      $mode 
     */
    private function saveMenu(Menu $menu, string $mode, EntityManagerInterface $em): void{
        $em->persist($menu);
        $em->flush();
        
        if($mode == 'new') {
            $this->addFlash('success', 'Menu créé avec succès');
        } else {
            $this->addFlash('success', 'Menu mis à jour avec succès');
        }
    }    
}
