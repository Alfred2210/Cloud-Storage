<?php

namespace App\Controller;

use App\Entity\Facture;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Plan;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

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
        $user = $this->getUser();
        $planId = $request->query->get('planId');
        $em = $this->doctrine->getManager();
        $plan = $em->getRepository(Plan::class)->find($planId);






        if($user)
        {
            $customerEmail = $user->getMail();
        }
        else
        {
            $userData = $request->getSession()->get('pending_registration');
            $customerEmail = $userData ? $userData->getMail() : 'storage@contact.com';
        }



        if (!$plan) {
            throw $this->createNotFoundException('Vous n\'avez pas de plan');
        }

        $prix = $plan->getPrix();
        $nom = $plan->getNom();

        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../../.env');





        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);
        $session = $stripe->checkout->sessions->create([
            'customer_email' => $customerEmail,
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $nom,

                        ],
                        'unit_amount' => $prix * 100,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'invoice_creation' => ['enabled' => true],
            'success_url' => 'http://localhost:8000/success?session_id={CHECKOUT_SESSION_ID}',
            'shipping_address_collection' => [
                'allowed_countries' => ['FR']
            ],
            'phone_number_collection' => [
                'enabled' => true
            ],
        ]);



        $request->getSession()->set('pending_upgrade_plan', $planId);








        return $this->redirect($session->url);
    }
    private function handleStripeSession($sessionId, $request) {
        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);
        $session = $stripe->checkout->sessions->retrieve($sessionId);
        $customerDetails = $session->customer_details;
        $address = $customerDetails->address;

        $data = [
            'phone_number' => $customerDetails->phone,
            'city' => $address->city,
            'zip_code' => $address->postal_code,
            'shipping_address' => $address->line1,
            'name' => $customerDetails->name,
            'price' => $session->amount_total / 100,

        ];

        // Calculate 'tva' and 'ttc' after 'price' is set
        $data['tva'] = $data['price'] * 0.2;
        $data['ttc'] = $data['price'] + $data['tva'];

        foreach ($data as $key => $value) {
            $request->getSession()->set($key, $value);
        }

        return $data;
    }

    #[Route('/success', name: 'app_stripe_success')]
    public function success(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = $this->getUser();
        $planId = $request->getSession()->get('pending_upgrade_plan');
        $userData = $request->getSession()->get('pending_registration');
        $sessionId = $request->query->get('session_id');
        $this->handleStripeSession($sessionId, $request);

        $em = $this->doctrine->getManager();

        if ($user && $planId) {
            $plan = $entityManager->getRepository(Plan::class)->find($planId);

            if ($plan) {
                $user->setPlan($plan);

                // Créez une nouvelle facture à chaque paiement
                $facture = new Facture();
                $facture->setUser($user);
                $facture->setPrice($plan->getPrix());
                $facture->setDate(new \DateTime());

                $entityManager->persist($facture);
                $entityManager->flush();
            }

            $emailForUser = (new Email())
                ->from('storage@contact.com')
                ->to($user->getMail())
                ->subject('Cloud Storage')
                ->text('Cloud Storage')
                ->html('<>Bienvenue sur Cloud Storage, votre stockage a été augmenté.</>');

            $mailer->send($emailForUser);
        }

        if ($userData) {
            $user = new User();
            $user->setMail($userData->getMail());
            $user->setPassword($userData->getPassword());
            $user->setNom($userData->getNom());
            $user->setPrenom($userData->getPrenom());
            $user->setRoles(['ROLE_USER']);

            $planId = $userData->getPlan();
            $plan = $entityManager->getRepository(Plan::class)->find($planId);

            $facture = new Facture();
            $facture->setUser($user);
            $facture->setPrice($plan->getPrix());
            $facture->setDate(new \DateTime());

            $emailForUserData = (new Email())
                ->from('storage@contact.com')
                ->to($userData->getMail())
                ->subject('Cloud Storage')
                ->text('Bienvenue sur Cloud Storage')
                ->html('<p>Bienvenue sur Cloud Storage, votre compte a été activé.</p>');

            $mailer->send($emailForUserData);

            if ($plan) {
                $user->setPlan($plan);
            }

            $entityManager->persist($user);
            $entityManager->persist($facture);
            $entityManager->flush();
            $request->getSession()->remove('pending_registration');
        }

        return $this->render('stripe/success.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

}
