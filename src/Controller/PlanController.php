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
                '20 GO Stockage',
                '2 User',
                '1 Project',
                '1000 Messages',
                'Secure Online Transfer',
            ],
            'Pro' => [
                '50 GO Stockage',
                '5 User',
                '10 Project',
                '5000 Messages',
                'Secure Online Transfer',
            ],
            'Business' => [
                '100 GO Stockage',
                '10 User',
                '20 Project',
                'Support 24/7',
                'Secure Online Transfer',
                'Secure Account',
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
