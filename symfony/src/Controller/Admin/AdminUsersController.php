<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminUsersController extends AbstractController
{
    #[Route('/users', name: 'app_admin_users')]
    public function index(Request $request, UserRepository $userRepo): Response
    {
        $search = $request->query->get('q', '');
        $role   = $request->query->get('role', '');

        // Load all users then filter in PHP
        // (swap to a DQL query in UserRepository for large datasets)
        $users = $userRepo->findAll();

        if ($search) {
            $q     = strtolower($search);
            $users = array_filter($users, function ($u) use ($q) {
                return str_contains(strtolower($u->getEmail()), $q)
                    || str_contains(strtolower($u->getNom()), $q)
                    || str_contains(strtolower($u->getPrenom()), $q);
            });
        }

        if ($role) {
            $users = array_filter($users, fn($u) => in_array($role, $u->getRoles()));
        }

        return $this->render('admin/users.html.twig', [
            'users' => array_values($users),
        ]);
    }
}
