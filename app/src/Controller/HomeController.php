<?php

namespace App\Controller;

use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(AvisRepository $avisRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'avis_list' => $avisRepository->findValides(),
        ]);
    }
}
