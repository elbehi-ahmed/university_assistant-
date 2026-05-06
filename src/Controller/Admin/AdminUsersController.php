<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminUsersController extends AbstractController
{
    #[Route('/users', name: 'app_admin_users')]
    public function list(Request $request, EntityManagerInterface $em): Response
    {
        $admin = $this->getAdmin($em);
        if (!$admin) {
            throw $this->createNotFoundException('No admin account found.');
        }

        $roleFilter = $request->query->get('role', '');
        $searchQuery = $request->query->get('q', '');

        $qb = $em->getRepository(User::class)->createQueryBuilder('u');

        if ($roleFilter) {
            $qb->andWhere('u.roles LIKE :role')
               ->setParameter('role', '%' . $roleFilter . '%');
        }

        if ($searchQuery) {
            $qb->andWhere('u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q')
               ->setParameter('q', '%' . $searchQuery . '%');
        }

        $users = $qb->getQuery()->getResult();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/create', name: 'app_admin_user_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        // TODO: Implement user creation form
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/edit', name: 'app_admin_user_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        // TODO: Implement user edit form
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if ($user) {
            $em->remove($user);
            $em->flush();
        }
        return $this->redirectToRoute('app_admin_users');
    }

    private function getAdmin(EntityManagerInterface $em): ?User
    {
        $users = $em->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                return $user;
            }
        }
        return $users[0] ?? null;
    }
}