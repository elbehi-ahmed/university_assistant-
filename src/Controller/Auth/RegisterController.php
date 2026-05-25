<?php

namespace App\Controller\Auth;

use App\Entity\Classe;
use App\Entity\AllowedEmail;
use App\Entity\User;
use App\Repository\AllowedEmailRepository;
use App\Repository\ClasseRepository;
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
        ClasseRepository            $classeRepository,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        // Already logged in → redirect to correct dashboard
        if ($this->getUser()) {
            return $this->redirectToUserDashboard();
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $firstname = trim($request->request->get('firstname', ''));
            $lastname  = trim($request->request->get('lastname', ''));
            $email     = strtolower(trim($request->request->get('email', '')));
            $password  = $request->request->get('password', '');
            $confirm   = $request->request->get('confirm', '');
            $terms     = $request->request->get('terms');

            // ── Basic validation ──────────────────────────────────
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
                // ── Check whitelist ───────────────────────────────
                /** @var AllowedEmail|null $allowed */
                $allowed = $allowedEmailRepository->findOneBy(['email' => $email]);

                if (!$allowed) {
                    $error = 'This email address is not authorized to register. Please contact an administrator.';
                } elseif ($allowed->isRegistered()) {
                    $error = 'An account already exists for this email address.';
                } else {
                    // ── Create user ───────────────────────────────
                    $user = new User();
                    $user->setPrenom($firstname);
                    $user->setNom($lastname);
                    $user->setEmail($email);
                    $user->setRoles([$allowed->getRole()]);
                    $user->setPassword($hasher->hashPassword($user, $password));

                    if ($allowed->getRole() === 'ROLE_STUDENT') {
                        $classCode = trim((string) $allowed->getClasse());

                        if ($classCode === '') {
                            $error = 'Please assign a class to this student email in the whitelist first.';
                        } else {
                            $studentClass = $this->resolveStudentClass($classCode, $classeRepository);

                            if ($studentClass === null) {
                                $error = sprintf('No class matches "%s".', $classCode);
                            } else {
                                $user->addClass($studentClass);
                            }
                        }
                    }

                    if ($error !== null) {
                        return $this->render('auth/register.html.twig', [
                            'error' => $error,
                        ]);
                    }

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

    /**
     * BUG FIX: original code always redirected to app_student_dashboard,
     * even for teachers and admins who just registered.
     */
    private function redirectToUserDashboard(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin_dashboard');
        }
        if ($this->isGranted('ROLE_TEACHER')) {
            return $this->redirectToRoute('app_teacher_dashboard');
        }
        return $this->redirectToRoute('app_student_dashboard');
    }

    private function resolveStudentClass(string $classCode, ClasseRepository $classeRepository): ?Classe
    {
        $normalizedCode = $this->normalizeClassIdentifier($classCode);

        foreach ($classeRepository->findAll() as $classe) {
            $storedCode = $this->normalizeClassIdentifier((string) $classe->getCode());
            $storedName = $this->normalizeClassIdentifier((string) $classe->getNom());

            if ($storedCode === $normalizedCode || $storedName === $normalizedCode) {
                return $classe;
            }
        }

        return null;
    }

    private function normalizeClassIdentifier(string $value): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', trim($value)) ?: '');
    }
}
