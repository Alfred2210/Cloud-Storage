<?php

namespace App\Controller;

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

        $plans = $this->planRepository->findAll();

        return $this->render('plan/plan.html.twig', [
            'plans' => $plans,
        ]);
    }
}
