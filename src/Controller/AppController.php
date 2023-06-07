<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route('/app', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('app/app.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }
}
