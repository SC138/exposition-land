<?php

namespace App\Controller;

use App\Repository\PicturesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'accueil')]
    public function home(PicturesRepository $picturesRepository)
    {
        $pictures = $picturesRepository ->findAll();
        return $this->render("home.html.twig", ['pictures'=> $pictures]);
    }

}

