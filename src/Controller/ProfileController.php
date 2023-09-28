<?php


namespace App\Controller;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();

        $firstName = $user->getPrenom();
        $lastName = $user->getNom();
        $mail = $user->getMail();
        $plan = $user->getPlan();

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'firstName' => $firstName,
            'lastName' => $lastName,
            'mail' => $mail,
            'plan' => $plan,
        ]);
    }
}
