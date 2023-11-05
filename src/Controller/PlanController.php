<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PlanRepository;

class PlanController extends AbstractController
{
private $planRepository;


    public function __construct(PlanRepository $planRepository)
    {
        $this->planRepository = $planRepository;
    }


    #[Route('/plan', name: 'app_plan')]
    public function index(): Response
    {

        $user = $this->getUser();
        $plans = $this->planRepository->findAll();

        $planDetails = [
            'Standard' => [
                '20 GO Stockages',
                '2 Utilisateurs',
                '1 Projet',
                '1000 Messages',
                'Transfert en ligne sécurisé',
            ],
            'Pro' => [
                '50 GO Stockages',
                '5 Utilisateurs',
                '10 Project',
                '5000 Messages',
                'Transfert en ligne sécurisé',
            ],
            'Business' => [
                '100 GO Stockages',
                '10 Utilisateurs',
                '20 Projets',
                'Support 24/7',
                'Transfert en ligne sécurisé',
                'Compte securisé',
            ],
        ];



        return $this->render('plan/plan.html.twig', [
            'plans' => $plans,
            'planDetails' => $planDetails,
            'user' => $user,
        ]);
    }



    /*#[Route('/plan/upgrade/success', name: 'app_plan_upgrade_success')]
    public function upgradeSuccess(): Response
    {
        return $this->render('plan/upgrade_success.html.twig');
    }*/




}
