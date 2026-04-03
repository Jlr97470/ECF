<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Avis;
use App\Form\AvisType;
class AvisController extends AbstractController
{
    #[Route('/avis/liste', name: 'app_avis_liste')]
    public function liste(EntityManagerInterface $em, PaginatorInterface $paginator,Request $request): Response
    {
        // On récupère tous les articles disponibles en base de données
        $query = $em->createQuery('SELECT avis FROM App\Entity\Avis avis');
            
        $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /* page number */
        10 /* limit per page */
        );          

        return $this->render('avis/liste.html.twig', [
            'pagination' => $pagination
        ]);
    }    

    #[Route('/avis/index/{id}', name: 'app_avis_index')]
    public function index(EntityManagerInterface $em,int $id): Response
    {
        // On récupère l'Avis qui correspond à l'id passé dans l'url
        $avis = $em->getRepository(Avis::class)->findOneBy(['avis_id' => $id]);
      
        return $this->render('avis/index.html.twig', [
            'avis' => $avis,
        ]);
    }

    #[Route('/avis/add', name: 'app_avis_add')]
    public function add(EntityManagerInterface $em, Request $request): Response
    {
        $mode       = 'new';
        $avis    = new Avis();

        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->saveAvis($avis, $mode,$em);

            return $this->redirectToRoute('app_avis_liste');
        }

        $parameters = array(
            'form'      => $form->createView(),
            'Avis'      => $avis,
            'mode'      => $mode
        );

        return $this->render('avis/edit.html.twig', $parameters);
    }

    #[Route('/avis/edit/{id}', name: 'app_avis_edit')]
    public function edit(EntityManagerInterface $em, Request $request, int $id=null): Response
    {
        $mode = 'update';
        // On récupère l'Avis qui correspond à l'id passé dans l'url
        $avis = $em->getRepository(Avis::class)->findOneBy(['avis_id' => $id]);

        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           
            $this->saveAvis($avis, $mode,$em);

            return $this->redirectToRoute('app_avis_liste');
        }

        $parameters = array(
            'form'      => $form->createView(),
            'avis'      => $avis,
            'mode'      => $mode
        );

        return $this->render('avis/edit.html.twig', $parameters);
    }

    #[Route('/avis/remove/{id}', name: 'app_avis_remove')]
    public function remove(EntityManagerInterface $em, int $id): Response
    {
        // On récupère l'Avis qui correspond à l'id passé dans l'URL
        $avis = $em->getRepository(Avis::class)->findOneBy(['avis_id' => $id]);

        // L'Avis est supprimé
        $em->remove($avis);
        $em->flush();

        $this->addFlash('success', 'L"Avis a été supprimé avec succès');

        return $this->redirectToRoute('app_avis_liste');
    }

    /**
     * Enregistrer un Avis en base de données
     * 
     * @param   Avis     $avis
     * @param   string      $mode 
     */
    private function saveAvis(Avis $avis, string $mode, EntityManagerInterface $em){

        $em->persist($avis);
        $em->flush();
        if($mode == 'new') {
            $this->addFlash('success', 'L"Avis a été ajouté avec succès');
        } else {
            $this->addFlash('success', 'L"Avis a été modifié avec succès');
        }
    }    

}
