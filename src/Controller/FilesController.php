<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;


class FilesController extends AbstractController
{


    #[Route('/files', name: 'app_files')]
    public function index(EntityManagerInterface $em): Response
    {

        $files = $em->getRepository(File::class)->findBy(['user' => $this->getUser()]);

        return $this->render('files/files.html.twig', [
            'controller_name' => 'FilesController',
            'files' => $files
        ]);
    }

    #[Route('/files/upload', name: 'app_files_upload', methods: ['POST'])]
    public function uploadFiles(Request $request, EntityManagerInterface $entityManager): Response
    {
        $file = $request->files->get('file');

        if (!$file) {
            throw $this->createNotFoundException('No file found');
        }

        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();

        $user = $this->getUser();

        $fileEntity = new File();
        $fileEntity->setNom($fileName);
        $fileEntity->setTaille($file->getSize());
        $fileEntity->setUser($user);

        $entityManager->persist($fileEntity);
        $entityManager->flush();
        

        return $this->redirectToRoute('app_files');
    }
}
