<?php

namespace App\Controller\Admin;

use App\Entity\AllowedEmail;
use App\Repository\AllowedEmailRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminWhitelistController extends AbstractController
{
    #[Route('/whitelist', name: 'app_admin_whitelist', methods: ['GET', 'POST'])]
    public function index(
        Request                $request,
        AllowedEmailRepository $repo,
        EntityManagerInterface $em
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('admin_whitelist_add', (string) $request->request->get('_token'))) {
                $this->addFlash('error', 'Invalid request, please try again.');
                return $this->redirectToRoute('app_admin_whitelist');
            }

            $email = strtolower(trim($request->request->get('email', '')));
            $role = trim((string) $request->request->get('role', 'ROLE_STUDENT'));
            $departement = trim((string) $request->request->get('departement', ''));
            $classe = trim((string) $request->request->get('classe', ''));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Invalid email address.');
            } elseif ($repo->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'This email is already in the whitelist.');
            } else {
                $allowed = new AllowedEmail();
                $allowed->setEmail($email);
                $allowed->setRole($role);
                $allowed->setDepartement($departement !== '' ? $departement : null);
                $allowed->setClasse($classe !== '' ? $classe : null);
                $em->persist($allowed);
                $em->flush();
                $this->addFlash('success', 'Email ' . $email . ' added to whitelist.');
            }

            return $this->redirectToRoute('app_admin_whitelist');
        }

        $search = $request->query->get('q', '');
        $all    = $repo->findAll();

        if ($search) {
            $q   = strtolower($search);
            $all = array_filter($all, fn($a) => str_contains(strtolower($a->getEmail()), $q));
        }

        return $this->render('admin/whitelist.html.twig', [
            'allowed_emails' => array_values($all),
        ]);
    }

    #[Route('/whitelist/{id}/revoke', name: 'app_admin_whitelist_revoke', methods: ['POST'])]
    public function revoke(
        int                    $id,
        Request                $request,
        AllowedEmailRepository $repo,
        UserRepository         $userRepo,
        EntityManagerInterface $em
    ): Response {
        $allowed = $repo->find($id);

        if (!$allowed) {
            $this->addFlash('error', 'Email entry not found.');
            return $this->redirectToRoute('app_admin_whitelist');
        }

        if (!$this->isCsrfTokenValid('delete_allowed_email_' . $id, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid delete request.');
            return $this->redirectToRoute('app_admin_whitelist');
        }

        $user = $userRepo->findOneBy(['email' => $allowed->getEmail()]);

        if ($user) {
            $em->remove($user);
        }

        $em->remove($allowed);
        $em->flush();

        $message = 'Access revoked for ' . $allowed->getEmail();

        if ($user) {
            $message .= ' and the matching user account was deleted.';
        }

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_admin_whitelist');
    }
}
