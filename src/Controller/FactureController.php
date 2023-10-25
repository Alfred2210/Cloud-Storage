<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Entity\Plan;
use Doctrine\ORM\EntityManagerInterface;
use Mpdf\Mpdf;
use Stripe\StripeClient;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

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
    public function showInvoice($invoiceId,Request $request): Response
    {
        $user = $this->getUser();
        $date = new \DateTime();
        $date = $date->format('d-m-Y');


        $facture = $this->entityManager->getRepository(Facture::class)->find($invoiceId);
        $firstName = $user->getPrenom();
        $mail = $user->getMail();
        $session = $request->getSession();
        $phoneNumber = $session->get('phone_number');
        $city = $session->get('city');
        $zipCode = $session->get('zip_code');
        $shippingAddress = $session->get('shipping_address');
        $name = $session->get('name');
        $price = $session->get('price');
        $tva = $session->get('tva');
        $ttc = $session->get('ttc');



        if (!$facture) {
            throw $this->createNotFoundException('Facture non trouvée');
        }




        $invoiceContent = $this->renderView('facture/index.html.twig', [
            'facture' => $facture, // Passez la facture au modèle Twig
            'firstName' => $firstName,
            'mail' => $mail,
            'city' => $city,
            'zipCode' => $zipCode,
            'shippingAddress' => $shippingAddress,
            'phoneNumber' => $phoneNumber,
            'name' => $name,
            'price' => $price,
            'tva' => $tva,
            'ttc' => $ttc,
            'date' => $date,

        ]);

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($invoiceContent);

        $response = new Response($mpdf->Output('facture.pdf', 'D'));

        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }



}