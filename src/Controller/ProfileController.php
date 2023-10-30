<?php


namespace App\Controller;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends AbstractController
{

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();

        $firstName = $user->getPrenom();
        $lastName = $user->getNom();
        $mail = $user->getMail();
        $plan = $user->getPlan();
        $factures = $user->getFactures();

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'firstName' => $firstName,
            'lastName' => $lastName,
            'mail' => $mail,
            'plan' => $plan,
            'factures' => $factures,
        ]);
    }

    #[Route('/user/{id/delete}', name: 'app_user_deletes', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route('/profile/delete', name: 'app_profile_delete')]
    public function deleteAccount(Request $request, CsrfTokenManagerInterface $csrfTokenManager, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $token = $request->request->get('_token');

            if ($csrfTokenManager->isTokenValid(new CsrfToken('delete_user', $token))) {

                $entityManager->remove($user);
                $entityManager->flush();
                $this->tokenStorage->setToken(null);

                return $this->redirectToRoute('app_login');
            } else {

                return $this->redirectToRoute('app_profile');
            }
        }
        return $this->render('profile/delete.html.twig', [
            'user' => $user,
        ]);
    }

}