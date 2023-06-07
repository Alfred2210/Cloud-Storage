<?php

namespace App\Controller;

use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Plan;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

class StripeController extends AbstractController
{

        private ManagerRegistry $doctrine;

        public function __construct(ManagerRegistry $doctrine)
        {
            $this->doctrine = $doctrine;
        }

    #[Route('/stripe', name: 'app_stripe')]
    public function index(): Response
    {
        return $this->render('stripe/stripe.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

    #[Route('create-checkout-session', name: 'app_stripe_checkout')]
    public function checkout(Request $request): Response
    {
        $planId = $request->query->get('planId');
        $em = $this->doctrine->getManager();
        $plan = $em->getRepository(Plan::class)->find($planId);

        if(!$plan)
            throw $this->createNotFoundException('Vous n\'avez pas de plan');
        
        $prix = $plan->getPrix();
        $nom = $plan->getNom();

        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);
        $session = $stripe->checkout->sessions->create([
            'customer_email' => $this->getUser()->getUserIdentifier(),
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $nom,
                        ],
                        'unit_amount' => $prix * 100,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => 'http://localhost:8000/success',
        ]);

          return $this->redirect($session->url);

    }



    #[Route('/success', name: 'app_stripe_success')]
    public function success(): Response
    {

        $user = $this->getUser();


        return $this->render('stripe/success.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }


}
