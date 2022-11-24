<?php

namespace App\Controller;

use App\Entity\Pictures;
use App\Form\PicturesType;
use App\Repository\PicturesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/pictures')]
class PicturesController extends AbstractController
{
    #[Route('/', name: 'app_pictures_index', methods: ['GET'])]
    public function index(PicturesRepository $picturesRepository): Response
    {
        return $this->render('pictures/index.html.twig', [
            'pictures' => $picturesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_pictures_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PicturesRepository $picturesRepository, SluggerInterface $slugger): Response
    {
        $picture = new Pictures();
        $form = $this->createForm(PicturesType::class, $picture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($imageFile = $form->get('PicturesFiles')->getData()){
                // Je récupère le fichier image depuis le formulaire
                // Je récupère le nom original du fichier
                $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // J'utilise une instance de la classe slugger et sa méthode slug
                // pour supprimer les caractères spéciaux et espace du nom de fichier
                $safeFileName = $slugger->slug($originalFileName);
                //Je rajoute au nom de fichier, un identifiant unique (en cas de doublon)
                $fileName = $safeFileName.'-'.uniqid().'.'.$imageFile->guessExtension();
                //Je déplace l'image dans le dossier public une fois renommée avec le nom créé
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );
                $picture->setPicturesFiles($fileName);
            }
            $picturesRepository->save($picture, true);

            return $this->redirectToRoute('app_pictures_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('pictures/new.html.twig', [
            'picture' => $picture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pictures_show', methods: ['GET'])]
    public function show(Pictures $picture): Response
    {
        return $this->render('pictures/show.html.twig', [
            'picture' => $picture,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pictures_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pictures $picture, PicturesRepository $picturesRepository): Response
    {
        $form = $this->createForm(PicturesType::class, $picture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picturesRepository->save($picture, true);

            return $this->redirectToRoute('app_pictures_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('pictures/edit.html.twig', [
            'picture' => $picture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pictures_delete', methods: ['POST'])]
    public function delete(Request $request, Pictures $picture, PicturesRepository $picturesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$picture->getId(), $request->request->get('_token'))) {
            $picturesRepository->remove($picture, true);
        }

        return $this->redirectToRoute('app_pictures_index', [], Response::HTTP_SEE_OTHER);
    }
}
