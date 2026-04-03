<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Plat;
use App\Entity\Contient;
use App\Entity\Allergene;
use App\Form\PlatType;
class PlatController extends AbstractController
{
    #[Route('/plat/photo/{id}', name: 'app_plat_photo')]
    public function Photo(EntityManagerInterface $em,int $id): Response
    {
        // On récupère l'plat qui correspond à l'id passé dans l'url
        $plat = $em->getRepository(Plat::class)->findOneBy(['plat_id' => $id]);

        return new Response(stream_get_contents($plat->getPhoto()), 200, ['Content-Type' => 'image/jpeg']);
    }

    #[Route('/plat/liste', name: 'app_plat_liste')]
    public function liste(EntityManagerInterface $em, PaginatorInterface $paginator,Request $request): Response
    {
        // On récupère tous les articles disponibles en base de données
        $query = $em->createQuery('SELECT plat FROM App\Entity\Plat plat');
            
        $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /* page number */
        10 /* limit per page */
        );          

        return $this->render('plat/liste.html.twig', [
            'pagination' => $pagination
        ]);
    }    

    #[Route('/plat/index/{id}', name: 'app_plat_index')]
    public function index(EntityManagerInterface $em,PaginatorInterface $paginator, Request $request,int $id): Response
    {
        // On récupère l'plat qui correspond à l'id passé dans l'url
        $plat = $em->getRepository(Plat::class)->findOneBy(['plat_id' => $id]);

        $allergenes = $plat->getContients()->map(function($contient) {
            return $contient->getAllergeneId();
        })->toArray();  
        
        if ($allergenes) {
            // On récupère tous les articles disponibles en base de données
            $query = $em->createQueryBuilder()
                ->select('allergene')
                ->from(Allergene::class, 'allergene')
                ->where('allergene.allergene_id NOT IN (:allergenes)')
                ->setParameter('allergenes', $allergenes);
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('allergene')
                ->from(Allergene::class, 'allergene');
        }

        $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /* page number */
        10 /* limit per page */
        );            

        return $this->render('plat/index.html.twig', [
            'plat' => $plat,
            'allergenes' => $allergenes,
            'pagination' => $pagination
        ]);
    }

    #[Route('/plat/add', name: 'app_plat_add')]
    public function add(EntityManagerInterface $em, Request $request): Response
    {
        $mode       = 'new';
        $plat    = new plat();

        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('file')->getData();   
 
            if ($image) {
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
 
                $image->move(
                    $this->getParameter('upload_directory'),
                    $fichier, 
                );
            }

            $plat->setPhoto( file_get_contents($this->getParameter('images_directory') . '/' . $fichier)  );

            $this->addFlash('success', 'Image téléchargée avec succès');

            $this->saveplat($plat, $mode,$em);

            return $this->redirectToRoute('app_plat_liste');
        }

        $parameters = array(
            'form'      => $form->createView(),
            'plat'      => $plat,
            'mode'      => $mode
        );

        return $this->render('plat/edit.html.twig', $parameters);
    }

    #[Route('/plat/edit/{id}', name: 'app_plat_edit')]
    public function edit(EntityManagerInterface $em, Request $request, int $id=null): Response
    {
        $mode = 'update';
        // On récupère l'plat qui correspond à l'id passé dans l'url
        $plat = $em->getRepository(plat::class)->findOneBy(['plat_id' => $id]);

        $form = $this->createForm(platType::class, $plat);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('file')->getData(); 
            
            if ($image) {
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
 
                $image->move(
                    $this->getParameter('upload_directory'),
                    $fichier, 
                );
            }            
  
            $plat->setPhoto(  file_get_contents($this->getParameter('images_directory') . '/' . $fichier) );
            
            $this->addFlash('success', 'Image téléchargée avec succès');           

            $this->saveplat($plat, $mode,$em);

            return $this->redirectToRoute('app_plat_liste');
        }

        $parameters = array(
            'form'      => $form->createView(),
            'plat'      => $plat,
            'mode'      => $mode
        );

        return $this->render('plat/edit.html.twig', $parameters);
    }

    #[Route('/plat/remove/{id}', name: 'app_plat_remove')]
    public function remove(EntityManagerInterface $em, int $id): Response
    {
        // On récupère l'plat qui correspond à l'id passé dans l'URL
        $plat = $em->getRepository(Plat::class)->findOneBy(['plat_id' => $id]);

        // L'plat est supprimé
        $em->remove($plat);
        $em->flush();

        $this->addFlash('success', 'Le plat a été supprimé avec succès');

        return $this->redirectToRoute('app_plat_liste');
    }

    #[Route('/plat/allergeneadd/{idplat}/{idallergene}', name: 'app_plat_allergeneadd')]
    public function platadd(EntityManagerInterface $em, int $idplat, int $idallergene): Response
    {
        // On récupère l'Menu qui correspond à l'id passé dans l'url
        $plat=  $em->getRepository(Plat::class)->findOneBy(['plat_id' => $idplat]);

        $allergene = $em->getRepository(Allergene::class)->findOneBy(['allergene_id' => $idallergene]);

        $contient = new Contient();
        $contient->setPlatId($plat);
        $contient->setAllergeneId($allergene);
        $em->persist($contient);
        $em->flush();     

        $this->addFlash('success', 'Le allergène a été ajouté avec succès');

        return $this->redirectToRoute('app_plat_index', ['id' => $idplat]);
    }   
    
      #[Route('/plat/allergeneremove/{idplat}/{idallergene}', name: 'app_plat_allergeneremove')]
    public function allergeneremove(EntityManagerInterface $em, int $idplat, int $idallergene): Response
    {
        // On récupère l'Plat qui correspond à l'id passé dans l'url
        $plat = $em->getRepository(Plat::class)->findOneBy(['plat_id' => $idplat]);

        $contient = $plat->getContients()->filter(function($contient) use ($idallergene) {
            return $contient->getAllergeneId()->getAllergeneId() === $idallergene;
        })->first();

        if ($contient) {
            $em->remove($contient);
            $em->flush();
        }

        $this->addFlash('success', 'Le allergène a été supprimé avec succès');

        return $this->redirectToRoute('app_plat_index', ['id' => $idplat]);
    }     

    /**
     * Enregistrer un plat en base de données
     * 
     * @param   plat     $plat
     * @param   string      $mode 
     */
    private function saveplat(Plat $plat, string $mode, EntityManagerInterface $em){

        $em->persist($plat);
        $em->flush();
        if($mode == 'new') {
            $this->addFlash('success', 'Le plat a été ajouté avec succès');
        } else {
            $this->addFlash('success', 'Le plat a été modifié avec succès');
        }
    }    

}
