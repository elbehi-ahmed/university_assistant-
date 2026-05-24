<?php

namespace App\Controller\Admin;

use App\Repository\AllowedEmailRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function index(
        UserRepository        $userRepo,
        AllowedEmailRepository $allowedEmailRepo
    ): Response {
        $allUsers = $userRepo->findAll();

        $totalStudents  = 0;
        $totalTeachers  = 0;
        $totalSuspended = 0; // à adapter si champ isActive ajouté à User

        foreach ($allUsers as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles())) continue;
            if (in_array('ROLE_TEACHER', $user->getRoles())) {
                $totalTeachers++;
            } else {
                $totalStudents++;
            }
        }

        $totalWhitelisted = $allowedEmailRepo->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'total_students'    => $totalStudents,
            'total_teachers'    => $totalTeachers,
            'total_suspended'   => $totalSuspended,
            'total_whitelisted' => $totalWhitelisted,
            'recent_activities' => [], // à implémenter avec un système de logs
        ]);
    }
}
