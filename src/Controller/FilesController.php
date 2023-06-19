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
    public function index(EntityManagerInterface $em): Response
    {

        $files = $em->getRepository(File::class)->findBy(['user' => $this->getUser()]);

        return $this->render('files/files.html.twig', [
            'controller_name' => 'FilesController',
            'files' => $files
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
            $pdf->SetImportUse();

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
}
