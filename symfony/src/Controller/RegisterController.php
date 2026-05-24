<?php

namespace App\Controller\Auth;

use App\Entity\AllowedEmail;
use App\Entity\User;
use App\Repository\AllowedEmailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request                     $request,
        AllowedEmailRepository      $allowedEmailRepository,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        // Déjà connecté → redirection
        if ($this->getUser()) {
            return $this->redirectToRoute('app_student_dashboard');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $firstname = trim($request->request->get('firstname', ''));
            $lastname  = trim($request->request->get('lastname', ''));
            $email     = strtolower(trim($request->request->get('email', '')));
            $password  = $request->request->get('password', '');
            $confirm   = $request->request->get('confirm', '');
            $terms     = $request->request->get('terms');

            // ── Validations basiques ──────────────────────────────
            if (!$firstname || !$lastname || !$email || !$password) {
                $error = 'All fields are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email address.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } elseif (!$terms) {
                $error = 'You must accept the Terms of Service.';
            } else {
                // ── Vérification dans la liste blanche ───────────
                /** @var AllowedEmail|null $allowed */
                $allowed = $allowedEmailRepository->findOneBy(['email' => $email]);

                if (!$allowed) {
                    $error = 'This email address is not authorized to register. Please contact an administrator.';
                } elseif ($allowed->isRegistered()) {
                    $error = 'An account already exists for this email address.';
                } else {
                    // ── Création de l'utilisateur ─────────────────
                    $user = new User();
                    $user->setPrenom($firstname);
                    $user->setNom($lastname);
                    $user->setEmail($email);
                    $user->setRoles([$allowed->getRole()]); // rôle défini dans la whitelist
                    $user->setPassword($hasher->hashPassword($user, $password));

                    // Marquer l'email comme utilisé
                    $allowed->setIsRegistered(true);

                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success', 'Account created! You can now sign in.');
                    return $this->redirectToRoute('app_login');
                }
            }
        }

        return $this->render('auth/register.html.twig', [
            'error' => $error,
        ]);
    }
}
