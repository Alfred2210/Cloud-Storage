<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Mpdf\Mpdf;

class FilesController extends AbstractController
{






    #[Route('/dashboard', name: 'app_files')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $sort = $request->query->get('sort', 'date');
        $order = $request->query->get('order', 'DESC');

        switch ($sort) {
            case 'name':
                $sortField = 'nom';
                break;
            case 'recent':
                $sortField = 'id';
                break;
            case 'size': // Add a case for sorting by file size
                $sortField = 'taille';
                break;
            default:
                $sortField = 'date';
                break;
        }

        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifier si l'utilisateur a un plan associé
        if (!$user || !$user->getPlan()) {
            throw $this->createNotFoundException('Plan not found for the user');
        }

        $files = $em->getRepository(File::class)->findBy(['user' => $user], [$sortField => $order]);


        $totalStorage = $user->getPlan()->getCapacite() * 1024 * 1024 * 1024;
        $usedSpace = 0;
        foreach ($files as $file) {
            $usedSpace += $file->getTaille();
        }

        return $this->render('files/files.html.twig', [
            'controller_name' => 'FilesController',
            'files' => $files,
            'sort' => $sort,
            'order' => $order,
            'totalStorage' => $totalStorage,
            'usedSpace' => $usedSpace
        ]);
    }


    #[Route('/dashboard/upload', name: 'app_files_upload', methods: ['POST'])]
    public function uploadFiles(Request $request, EntityManagerInterface $entityManager): Response
    {
        $file = $request->files->get('file');
        $dateTime = new \DateTime();
        $currentTimezone = new \DateTimeZone('UTC');
        $dateTime->setTimezone($currentTimezone);
        $mime = $file->getMimeType();

        $newTimezone = new \DateTimeZone('Europe/Paris');
        $dateTime->setTimezone($newTimezone);

        $fileName = $file->getClientOriginalName();

        $user = $this->getUser();

        $fileEntity = new File();
        $fileEntity->setNom($fileName);
        $fileEntity->setTaille($file->getSize());
        $fileEntity->setUser($user);
        $fileEntity->setDate($dateTime);
        $fileEntity->setType($mime);

        $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/', $fileName);


        if ($mime === 'application/pdf') {
            $pdfPath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $fileName;
            $pdf = new Mpdf();
            $pagesCount = $pdf->SetSourceFile($pdfPath);
            for ($page = 1; $page <= $pagesCount; $page++) {
                $pdf->AddPage();
                $importPage = $pdf->ImportPage($page);
                $pdf->UseTemplate($importPage);
            }

            $pdfPreviewPath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $fileName;
            $pdf->Output($pdfPreviewPath, 'F');
        }

        $entityManager->persist($fileEntity);
        $entityManager->flush();


        return $this->redirectToRoute('app_files');
    }


    #[Route('/dashboard/view/{id}', name: 'app_files_view')]

    public function viewFile(int $id, EntityManagerInterface $em): Response
    {
        $file = $em->getRepository(File::class)->find($id);

        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }

        $fileType = $file->getType();

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $file->getNom();
        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', $file->getType(),);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $file->getNom()

        );



        return $response;
    }

    #[Route('/dashboard/delete/{id}', name: 'app_files_delete', methods: ['POST'])]
    public function deleteFile(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $file = $em->getRepository(File::class)->find($id);

        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }

        $em->remove($file);
        $em->flush();

        return $this->redirectToRoute('app_files');
    }


    #[Route('/dashboard/delete-multiple', name: 'app_files_delete_multiple', methods: ['POST'])]
    public function deleteMultipleFiles(Request $request, EntityManagerInterface $em): Response
    {
        $fileIds = $request->request->get('selected_files');

        foreach ($fileIds as $fileId) {
            $file = $em->getRepository(File::class)->find($fileId);

            if ($file) {
                $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $file->getNom();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                $em->remove($file);
            }
        }

        $em->flush();
        return $this->redirectToRoute('app_files');
    }

    public function countFilesByClient()
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('
        SELECT COUNT(f.id) AS filesCount, u.username AS clientUsername
        FROM App\Entity\File f
        JOIN f.user u
        GROUP BY u.username
    ');

        return $query->getResult();
    }


}
