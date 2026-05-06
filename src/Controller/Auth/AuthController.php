<?php

namespace App\Controller\Auth;

use App\Entity\AllowedEmail;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    // ==========================================
    // LOGIN
    // ==========================================
    #[Route('/login', name: 'app_login')]
    public function login(Request $request): Response
    {
        // Symfony Security handles the actual authentication.
        // This controller just renders the form with any error from the previous attempt.
        
        $error = $request->getSession()->get('_security.last_error');
        $lastUsername = $request->getSession()->get('_security.last_username', '');

        return $this->render('auth/login.html.twig', [
            'error'         => $error,
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony Security intercepts this route — controller body never runs
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    // ==========================================
    // REGISTER
    // ==========================================
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $error = null;

        if ($request->isMethod('POST')) {
            $email     = trim($request->request->get('email', ''));
            $firstname = trim($request->request->get('firstname', ''));
            $lastname  = trim($request->request->get('lastname', ''));
            $password  = $request->request->get('password', '');
            $confirm   = $request->request->get('confirm', '');

            // ── Validation ──
            if (empty($email) || empty($firstname) || empty($lastname) || empty($password)) {
                $error = 'All fields are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email address.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                // ── Check whitelist ──
                $allowed = $em->getRepository(AllowedEmail::class)->findOneBy(['email' => $email]);
                if (!$allowed) {
                    $error = 'This email is not authorized to register. Contact administration.';
                } elseif ($allowed->isRegistered()) {
                    $error = 'An account already exists with this email.';
                } else {
                    // ── Check duplicate ──
                    $existing = $em->getRepository(User::class)->findOneBy(['email' => $email]);
                    if ($existing) {
                        $error = 'An account already exists with this email.';
                    } else {
                        // ── Create user ──
                        $user = new User();
                        $user->setEmail($email);
                        $user->setPrenom($firstname);
                        $user->setNom($lastname);
                        $user->setPassword(
                            $passwordHasher->hashPassword($user, $password)
                        );

                        // Role from whitelist or default student
                        $role = $allowed->getRole() ?: 'ROLE_STUDENT';
                        $user->setRoles([$role]);

                        // Mark as registered in whitelist
                        $allowed->setIsRegistered(true);

                        $em->persist($user);
                        $em->flush();

                        // ── Redirect to login with success ──
                        $this->addFlash('success', 'Account created! Please sign in.');
                        return $this->redirectToRoute('app_login');
                    }
                }
            }
        }

        return $this->render('auth/register.html.twig', [
            'error' => $error,
        ]);
    }
}