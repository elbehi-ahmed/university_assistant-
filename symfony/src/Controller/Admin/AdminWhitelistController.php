<?php

namespace App\Controller\Admin;

use App\Entity\AllowedEmail;
use App\Repository\AllowedEmailRepository;
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
        AllowedEmailRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $allowed = $repo->find($id);

        if (!$allowed) {
            $this->addFlash('error', 'Email entry not found.');
            return $this->redirectToRoute('app_admin_whitelist');
        }

        // Only revoke un-registered entries; delete if already used is an admin choice
        $em->remove($allowed);
        $em->flush();
        $this->addFlash('success', 'Access revoked for ' . $allowed->getEmail());

        return $this->redirectToRoute('app_admin_whitelist');
    }
}
