<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FrontController — homepage ONLY.
 * All other routes (student/teacher/admin/auth) are handled by their
 * dedicated controllers. Do NOT add routes here that duplicate those.
 */
class FrontController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // If already logged in, send to the right dashboard
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_admin_dashboard');
            }
            if ($this->isGranted('ROLE_TEACHER')) {
                return $this->redirectToRoute('app_teacher_dashboard');
            }
            return $this->redirectToRoute('app_student_dashboard');
        }

        return $this->redirectToRoute('app_login');
    }
}