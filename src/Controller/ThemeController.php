<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Theme;
use App\Entity\Menu;
use App\Entity\ProposeTheme;
use App\Form\ThemeType;
class ThemeController extends AbstractController
{
    #[Route('/theme/liste', name: 'app_theme_liste')]
    public function liste(EntityManagerInterface $em, PaginatorInterface $paginator,Request $request): Response
    {
        // On récupère tous les articles disponibles en base de données
        $query = $em->createQuery('SELECT theme FROM App\Entity\Theme theme');
            
        $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /* page number */
        10 /* limit per page */
        );          

        return $this->render('theme/liste.html.twig', [
            'pagination' => $pagination
        ]);
    }    

    #[Route('/theme/index/{id}', name: 'app_theme_index')]
    public function index(EntityManagerInterface $em,PaginatorInterface $paginator, Request $request,int $id): Response
    {
        // On récupère l'Theme qui correspond à l'id passé dans l'url
        $theme = $em->getRepository(Theme::class)->findOneBy(['theme_id' => $id]);

        $menus = $theme->getProposeThemes()->map(function($proposetheme) {
            return $proposetheme->getMenuId();
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

        return $this->render('theme/index.html.twig', [
            'theme' => $theme,
            'menus' => $menus,
            'pagination' => $pagination
        ]);
    }

    #[Route('/theme/add', name: 'app_theme_add')]
    public function add(EntityManagerInterface $em, Request $request): Response
    {
        $mode       = 'new';
        $theme    = new Theme();

        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->saveTheme($theme, $mode,$em);

            return $this->redirectToRoute('app_theme_liste');
        }

        $parameters = array(
            'form'      => $form->createView(),
            'Theme'      => $theme,
            'mode'      => $mode
        );

        return $this->render('theme/edit.html.twig', $parameters);
    }

    #[Route('/theme/edit/{id}', name: 'app_theme_edit')]
    public function edit(EntityManagerInterface $em, Request $request, int $id=null): Response
    {
        $mode = 'update';
        // On récupère l'Theme qui correspond à l'id passé dans l'url
        $theme = $em->getRepository(Theme::class)->findOneBy(['theme_id' => $id]);

        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           
            $this->saveTheme($theme, $mode,$em);

            return $this->redirectToRoute('app_theme_liste');
        }

        $parameters = array(
            'form'      => $form->createView(),
            'theme'      => $theme,
            'mode'      => $mode
        );

        return $this->render('theme/edit.html.twig', $parameters);
    }

    #[Route('/theme/remove/{id}', name: 'app_theme_remove')]
    public function remove(EntityManagerInterface $em, int $id): Response
    {
        // On récupère l'Theme qui correspond à l'id passé dans l'URL
        $theme = $em->getRepository(Theme::class)->findOneBy(['theme_id' => $id]);

        // L'Theme est supprimé
        $em->remove($theme);
        $em->flush();

        $this->addFlash('success', 'L"Theme a été supprimé avec succès');

        return $this->redirectToRoute('app_theme_liste');
    }
    #[Route('/theme/menuadd/{idtheme}/{idmenu}', name: 'app_theme_menuadd')]
    public function menuadd(EntityManagerInterface $em, int $idtheme, int $idmenu): Response
    {
        // On récupère le Theme qui correspond à l'id passé dans l'url
        $theme = $em->getRepository(Theme::class)->findOneBy(['theme_id' => $idtheme]);

        $menu=  $em->getRepository(Menu::class)->findOneBy(['menu_id' => $idmenu]);

        $proposetheme=  $menu->getProposeTheme();

        if ($proposetheme) {
            $proposetheme->setThemeId($theme);
            $em->persist($proposetheme);
            $em->flush();
        }
        else
        {
            $proposetheme = new ProposeTheme();
            $proposetheme->setMenuId($menu);
            $proposetheme->setThemeId($theme);
            $theme->addProposeTheme($proposetheme);
            $em->persist($theme);
            $em->flush();
        }

        $this->addFlash('success', 'Le menu a été ajouté avec succès');

        return $this->redirectToRoute('app_theme_index', ['id' => $idtheme]);
    }   
    
    #[Route('/theme/menuremove/{idtheme}/{idmenu}', name: 'app_theme_menuremove')]
    public function menuremove(EntityManagerInterface $em, int $idtheme, int $idmenu): Response
    {
        // On récupère le Theme qui correspond à l'id passé dans l'url
        $theme = $em->getRepository(Theme::class)->findOneBy(['theme_id' => $idtheme]);

        $proposetheme=  $theme->getProposeThemes()->filter(function($proposetheme) use ($idmenu) {
            return $proposetheme->getMenuId()->getMenuId() === $idmenu;
        })->first();

        if ($proposetheme) {
            $em->remove($proposetheme);
            $em->flush();
        }

        $this->addFlash('success', 'Le menu a été supprimé avec succès');

        return $this->redirectToRoute('app_theme_index', ['id' => $idtheme]);
    }     

    /**
     * Enregistrer un Theme en base de données
     * 
     * @param   Theme     $theme
     * @param   string      $mode 
     */
    private function saveTheme(Theme $theme, string $mode, EntityManagerInterface $em){

        $em->persist($theme);
        $em->flush();
        if($mode == 'new') {
            $this->addFlash('success', 'L"Theme a été ajouté avec succès');
        } else {
            $this->addFlash('success', 'L"Theme a été modifié avec succès');
        }
    }    

}
