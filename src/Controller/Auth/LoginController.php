<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    /**
     * Route : GET /login  → affiche le formulaire
     *         POST /login → géré automatiquement par Symfony Security (security.yaml)
     *
     * Si l'utilisateur est déjà connecté, on le redirige vers son espace.
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirection si déjà connecté
        if ($this->getUser()) {
            return $this->redirectToUserSpace();
        }

        return $this->render('auth/login.html.twig', [
            // Dernière erreur d'authentification (mauvais mot de passe, email inconnu…)
            'error'         => $authenticationUtils->getLastAuthenticationError(),
            // Pré-rempli l'email si l'utilisateur a déjà essayé de se connecter
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    /**
     * Route : GET/POST /logout → gérée par Symfony Security (security.yaml)
     * Cette méthode ne sera jamais exécutée, Symfony l'intercepte avant.
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method is intercepted by the firewall.');
    }

    // ──────────────────────────────────────────────
    // Helper : redirige vers l'espace du rôle actif
    // ──────────────────────────────────────────────
    private function redirectToUserSpace(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin_dashboard');
        }
        if ($this->isGranted('ROLE_TEACHER')) {
            return $this->redirectToRoute('app_teacher_dashboard');
        }
        return $this->redirectToRoute('app_student_dashboard');
    }
}
