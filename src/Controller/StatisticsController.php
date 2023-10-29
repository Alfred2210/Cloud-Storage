<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\File;
use Symfony\Component\Security\Core\Security;
use DateTime;

class StatisticsController extends AbstractController
{


    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/statistics', name: 'app_statistics')]
    public function index(Security $security): Response
    {
        $entityManager = $this->doctrine->getManager();

        // Le nombre total de fichiers uploadés
        $totalFiles = $entityManager->getRepository(File::class)->count([]);

        // Le nombre de fichiers uploadés aujourd'hui
        $today = new \DateTime('today');
        $filesToday = $entityManager->getRepository(File::class)
            ->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->where('f.date >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();


        $user = $security->getUser(); // Obtenez l'utilisateur actuellement connecté

        // Dans votre contrôleur
        if ($user) {
            $entityManager = $this->doctrine->getManager();

            $userFiles = $entityManager->getRepository(File::class)
                ->createQueryBuilder('f')
                ->select('COUNT(f)')
                ->where('f.user = :user') // Utilisez la relation user ici
                ->setParameter('user', $user)
                ->getQuery()
                ->getSingleScalarResult();
        } else {
            $userFiles = 0; // Aucun utilisateur n'est connecté, donc le nombre est 0
        }




        // La répartition du nombre de fichiers par client
        //$clientsFiles = $entityManager->getRepository(File::class)->countFilesByClient();

        $fileTypes = [
            'application/pdf' => 'filePdf',
            'image/jpeg' => 'fileJpg',
            'image/png' => 'filePng',
            'application/VND.OPENXMLFORMATS-OFFICEDOCUMENT.WORDPROCESSINGML.DOCUMENT' => 'fileDoc',
            'text/plain' => 'fileTxt'
        ];

        foreach ($fileTypes as $type => $FileType) {
            $$FileType = $entityManager->getRepository(File::class)->count(['type' => $type]);
        }

        return $this->render('statistics/index.html.twig', [
            'totalFiles' => $totalFiles,
            'filesToday' => $filesToday,
            'filePdf' => $filePdf,
            'fileJpg' => $fileJpg,
            'filePng' => $filePng,
            'fileDoc' => $fileDoc,
            'fileTxt' => $fileTxt,
            'userFiles' => $userFiles,

            //'clientsFiles' => $clientsFiles,
        ]);
    }
}
