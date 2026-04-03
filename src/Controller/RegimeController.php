<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Regime;
use App\Entity\Menu;
use App\Entity\Adapte;
use App\Form\RegimeType;
class RegimeController extends AbstractController
{
    #[Route('/regime/liste', name: 'app_regime_liste')]
    public function liste(EntityManagerInterface $em, PaginatorInterface $paginator,Request $request): Response
    {
        // On récupère tous les articles disponibles en base de données
        $query = $em->createQuery('SELECT regime FROM App\Entity\Regime regime');
            
        $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /* page number */
        10 /* limit per page */
        );          

        return $this->render('regime/liste.html.twig', [
            'pagination' => $pagination
        ]);
    }    

    #[Route('/regime/index/{id}', name: 'app_regime_index')]
    public function index(EntityManagerInterface $em,PaginatorInterface $paginator, Request $request,int $id): Response
    {
        // On récupère l'regime qui correspond à l'id passé dans l'url
        $regime = $em->getRepository(Regime::class)->findOneBy(['regime_id' => $id]);

        $menus = $regime->getAdaptes()->map(function($adapte) {
            return $adapte->getMenuId();
        })->toArray();

        if ($menus){
            // On récupère tous les articles disponibles en base de données
            $queryBuilder = $em->createQueryBuilder()
                ->select('menu')
                ->from(Menu::class, 'menu')
                ->where('menu.menu_id NOT IN (:menus)')
                ->setParameter('menus', $menus);
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

        return $this->render('regime/index.html.twig', [
            'regime' => $regime,
            'menus' => $menus,
            'pagination' => $pagination
        ]);
    }

    #[Route('/regime/add', name: 'app_regime_add')]
    public function add(EntityManagerInterface $em, Request $request): Response
    {
        $mode       = 'new';
        $regime    = new Regime();

        $form = $this->createForm(RegimeType::class, $regime);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->saveregime($regime, $mode,$em);

            return $this->redirectToRoute('app_regime_liste');
        }

        $parameters = array(
            'form'      => $form->createView(),
            'regime'      => $regime,
            'mode'      => $mode
        );

        return $this->render('regime/edit.html.twig', $parameters);
    }

    #[Route('/regime/edit/{id}', name: 'app_regime_edit')]
    public function edit(EntityManagerInterface $em, Request $request, int $id=null): Response
    {
        $mode = 'update';
        // On récupère l'regime qui correspond à l'id passé dans l'url
        $regime = $em->getRepository(regime::class)->findOneBy(['regime_id' => $id]);

        $form = $this->createForm(RegimeType::class, $regime);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           
            $this->saveregime($regime, $mode,$em);

            return $this->redirectToRoute('app_regime_liste');
        }

        $parameters = array(
            'form'      => $form->createView(),
            'regime'      => $regime,
            'mode'      => $mode
        );

        return $this->render('regime/edit.html.twig', $parameters);
    }

    #[Route('/regime/remove/{id}', name: 'app_regime_remove')]
    public function remove(EntityManagerInterface $em, int $id): Response
    {
        // On récupère l'regime qui correspond à l'id passé dans l'URL
        $regime = $em->getRepository(regime::class)->findOneBy(['regime_id' => $id]);

        // L'regime est supprimé
        $em->remove($regime);
        $em->flush();

        $this->addFlash('success', 'L"regime a été supprimé avec succès');

        return $this->redirectToRoute('app_regime_liste');
    }
    #[Route('/regime/menuadd/{idregime}/{idmenu}', name: 'app_regime_menuadd')]
    public function menuadd(EntityManagerInterface $em, int $idregime, int $idmenu): Response
    {
        // On récupère le regime qui correspond à l'id passé dans l'url
        $regime = $em->getRepository(regime::class)->findOneBy(['regime_id' => $idregime]);

        $menu=  $em->getRepository(Menu::class)->findOneBy(['menu_id' => $idmenu]);

        $adapte=  $menu->getAdapte();

        if ($adapte) {
            $adapte->setregimeId($regime);
            $em->persist($adapte);
            $em->flush();
        }
        else
        {
            $adapte = new Adapte();
            $adapte->setMenuId($menu);
            $adapte->setregimeId($regime);
            $regime->addAdapte($adapte);
            $em->persist($regime);
            $em->flush();
        }

        $this->addFlash('success', 'Le menu a été ajouté avec succès');

        return $this->redirectToRoute('app_regime_index', ['id' => $idregime]);
    }   
    
    #[Route('/regime/menuremove/{idregime}/{idmenu}', name: 'app_regime_menuremove')]
    public function menuremove(EntityManagerInterface $em, int $idregime, int $idmenu): Response
    {
        // On récupère le regime qui correspond à l'id passé dans l'url
        $regime = $em->getRepository(Regime::class)->findOneBy(['regime_id' => $idregime]);

        $adapte=  $regime->getAdaptes()->filter(function($adapte) use ($idmenu) {
            return $adapte->getMenuId()->getMenuId() === $idmenu;
        })->first();

        if ($adapte) {
            $em->remove($adapte);
            $em->flush();
        }

        $this->addFlash('success', 'Le menu a été supprimé avec succès');

        return $this->redirectToRoute('app_regime_index', ['id' => $idregime]);
    }     

    /**
     * Enregistrer un regime en base de données
     * 
     * @param   Regime     $regime
     * @param   string      $mode 
     */
    private function saveregime(Regime $regime, string $mode, EntityManagerInterface $em){

        $em->persist($regime);
        $em->flush();
        if($mode == 'new') {
            $this->addFlash('success', 'L"regime a été ajouté avec succès');
        } else {
            $this->addFlash('success', 'L"regime a été modifié avec succès');
        }
    }    

}

