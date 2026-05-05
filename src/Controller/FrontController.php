<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    // ==========================================
    // FRONT & AUTHENTICATION
    // ==========================================
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('front/index.html.twig');
    }

    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->render('auth/login.html.twig');
    }

    #[Route('/register', name: 'app_register')]
    public function register(): Response
    {
        return $this->render('auth/register.html.twig');
    }

    // ==========================================
    // ESPACE ÉTUDIANT (STUDENT)
    // ==========================================
    #[Route('/student/dashboard', name: 'app_student_dashboard')]
    public function studentDashboard(): Response
    {
        return $this->render('student/dashboard.html.twig');
    }

    #[Route('/student/subjects', name: 'app_student_subjects')]
    public function studentSubjects(): Response
    {
        return $this->render('student/matieres.html.twig');
    }

    #[Route('/student/exams', name: 'app_student_exams')]
    public function studentExams(): Response
    {
        return $this->render('student/exams.html.twig');
    }

    #[Route('/student/grades', name: 'app_student_grades')]
    public function studentGrades(): Response
    {
        return $this->render('student/grades.html.twig');
    }

    #[Route('/student/settings', name: 'app_student_settings')]
    public function studentSettings(): Response
    {
        return $this->render('student/student_settings.html.twig');
    }

    #[Route('/student/tasks', name: 'app_student_tasks')]
    public function studentTasks(): Response
    {
        return $this->render('student/tasks.html.twig');
    }
    
    // ==========================================
    // ESPACE PROFESSEUR (TEACHER)
    // ==========================================
    #[Route('/teacher/dashboard', name: 'app_teacher_dashboard')]
    public function teacherDashboard(): Response
    {
        return $this->render('teacher/dashboard.html.twig');
    }

    #[Route('/teacher/classes', name: 'app_teacher_classes')]
    public function teacherClasses(): Response
    {
        return $this->render('teacher/classes.html.twig');
    }

    #[Route('/teacher/tasks', name: 'app_teacher_tasks')]
    public function teacherTasks(): Response
    {
        return $this->render('teacher/tasks.html.twig');
    }

    #[Route('/teacher/grades', name: 'app_teacher_grades')]
    public function teacherGrades(): Response
    {
        return $this->render('teacher/grades.html.twig');
    }

    #[Route('/teacher/students', name: 'app_teacher_students')]
    public function teacherStudents(): Response
    {
        return $this->render('teacher/students.html.twig');
    }

    #[Route('/teacher/settings', name: 'app_teacher_settings')]
    public function teacherSettings(): Response
    {
        return $this->render('teacher/settings.html.twig');
    }

    // ==========================================
    // ESPACE ADMINISTRATEUR (ADMIN)
    // ==========================================
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function adminDashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function adminUsers(): Response
    {
        return $this->render('admin/users.html.twig');
    }

    #[Route('/admin/whitelist', name: 'app_admin_whitelist')]
    public function adminWhitelist(): Response
    {
        return $this->render('admin/whitelist.html.twig');
    }
    
    #[Route('/admin/settings', name: 'app_admin_settings')]
    public function adminSettings(): Response
    {
        return $this->render('admin/settings.html.twig');
    }
}
