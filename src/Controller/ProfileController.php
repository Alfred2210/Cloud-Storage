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
                // Supprimer l'utilisateur de la base de données
                $entityManager->remove($user);
                $entityManager->flush();

                // Déconnecter l'utilisateur
                // Use the injected service
                $this->tokenStorage->setToken(null);
                // Invalidate the session
                // Use the injected service
                // This line is not needed as the token storage will take care of it.
                //$this->get('session')->invalidate();

                // Rediriger vers la page d'accueil ou une autre page appropriée
                return $this->redirectToRoute('app_login'); // Remplacez 'app_home' par la route de votre choix.
            } else {
                // Gérer l'erreur CSRF (éventuellement rediriger vers une page d'erreur)
                return $this->redirectToRoute('app_profile'); // Rediriger vers la page de profil en cas d'erreur CSRF
            }
        }

        // Afficher la page de profil (ou une page de confirmation de suppression)
        return $this->render('profile/delete.html.twig', [
            'user' => $user,
        ]);
    }

}
