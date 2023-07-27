<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\File;
use DateTime;

class StatisticsController extends AbstractController
{


    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/statistics', name: 'app_statistics')]
    public function index(): Response
    {
        $entityManager = $this->doctrine->getManager();

        // Le nombre total de fichiers uploadés
        $totalFiles = $entityManager->getRepository(File::class)->count([]);

        // Le nombre de fichiers uploadés aujourd'hui
        $today = new DateTime('today');
        $filesToday = $entityManager->getRepository(File::class)->count(['date' => $today]);

        // La répartition du nombre de fichiers par client
        //$clientsFiles = $entityManager->getRepository(File::class)->countFilesByClient();

        return $this->render('statistics/index.html.twig', [
            'totalFiles' => $totalFiles,
            'filesToday' => $filesToday,
            //'clientsFiles' => $clientsFiles,
        ]);
    }
}
