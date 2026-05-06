<?php

namespace App\Controller\Admin;

use App\Entity\AllowedEmail;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'app_admin_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        $teacher = $this->getAdmin($em);
        if (!$teacher) {
            throw $this->createNotFoundException('No admin account found.');
        }

        // Count users by role
        $allUsers = $em->getRepository(User::class)->findAll();
        $totalStudents  = 0;
        $totalTeachers  = 0;
        $totalSuspended = 0;

        foreach ($allUsers as $user) {
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles, true)) {
                continue; // skip admins from counts
            }
            if (in_array('ROLE_TEACHER', $roles, true)) {
                $totalTeachers++;
            } else {
                $totalStudents++;
            }
            // If you add isActive/isSuspended to User, check here
        }

        $totalWhitelisted = count($em->getRepository(AllowedEmail::class)->findAll());

        // Recent activity from notifications or user creations
        $recentActivities = [];
        // Sort users by newest (we don't have createdAt, so use ID as proxy)
        usort($allUsers, fn($a, $b) => $b->getId() <=> $a->getId());
        $latestUsers = array_slice($allUsers, 0, 5);
        
        foreach ($latestUsers as $user) {
            $roles = $user->getRoles();
            $type = 'info';
            $desc = 'registered';
            
            if (in_array('ROLE_TEACHER', $roles, true)) {
                $type = 'gold';
                $desc = 'joined as teacher';
            } elseif (in_array('ROLE_STUDENT', $roles, true) || !in_array('ROLE_ADMIN', $roles, true)) {
                $type = 'success';
                $desc = 'joined as student';
            }

            $recentActivities[] = [
                'actor'       => $user->getPrenom() . ' ' . $user->getNom(),
                'description' => $desc,
                'type'        => $type,
                'createdAt'   => new \DateTime(), // placeholder since User has no createdAt
            ];
        }

        return $this->render('admin/dashboard.html.twig', [
            'total_students'   => $totalStudents,
            'total_teachers'   => $totalTeachers,
            'total_suspended'  => $totalSuspended,
            'total_whitelisted'=> $totalWhitelisted,
            'recent_activities'=> $recentActivities,
        ]);
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