<?php

namespace App\Controller;

use App\Entity\Facture;
use Doctrine\ORM\EntityManagerInterface;
use Mpdf\Mpdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FactureController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/facture', name: 'app_facture')]
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('facture/index.html.twig', [
            'controller_name' => 'FactureController',

        ]);
    }
    #[Route('/facture/{invoiceId}', name: 'app_facture_show')]
    public function showInvoice($invoiceId): Response
    {
        $user = $this->getUser();

        $facture = $this->entityManager->getRepository(Facture::class)->find($invoiceId);
        $firstName = $user->getPrenom();

        if (!$facture) {
            throw $this->createNotFoundException('Facture non trouvée');
        }

        // Maintenant que vous avez la facture, vous pouvez l'utiliser dans le modèle Twig
        $invoiceContent = $this->renderView('facture/index.html.twig', [
            'facture' => $facture, // Passez la facture au modèle Twig
            'firstName' => $firstName,
        ]);

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($invoiceContent);

        $response = new Response($mpdf->Output('facture.pdf', 'D'));

        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }



}