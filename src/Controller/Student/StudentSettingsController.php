<?php

namespace App\Controller\Student;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_STUDENT')]
#[Route('/student')]
class StudentSettingsController extends AbstractController
{
    #[Route('/settings', name: 'app_student_settings', methods: ['GET', 'POST'])]
    public function index(
        Request                     $request,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $now  = new \DateTime();

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');

            // ── Profile update ────────────────────────────────
            if ($action === 'profile') {
                $prenom = trim($request->request->get('prenom', ''));
                $nom    = trim($request->request->get('nom', ''));
                $email  = strtolower(trim($request->request->get('email', '')));

                if ($prenom && $nom && $email) {
                    $user->setPrenom($prenom);
                    $user->setNom($nom);
                    $user->setEmail($email);
                    $em->flush();
                    $this->addFlash('success', 'Profile updated successfully.');
                } else {
                    $this->addFlash('error', 'All fields are required.');
                }
            }

            // ── Password change ───────────────────────────────
            if ($action === 'password') {
                $current = $request->request->get('current_password', '');
                $new     = $request->request->get('new_password', '');
                $confirm = $request->request->get('confirm_password', '');

                if (!$hasher->isPasswordValid($user, $current)) {
                    $this->addFlash('error', 'Current password is incorrect.');
                } elseif (strlen($new) < 8) {
                    $this->addFlash('error', 'New password must be at least 8 characters.');
                } elseif ($new !== $confirm) {
                    $this->addFlash('error', 'Passwords do not match.');
                } else {
                    $user->setPassword($hasher->hashPassword($user, $new));
                    $em->flush();
                    $this->addFlash('success', 'Password updated successfully.');
                }
            }

            return $this->redirectToRoute('app_student_settings');
        }

        // ── Sidebar badges ────────────────────────────────────
        $urgentTaskCount   = 0;
        $upcomingExamCount = 0;
        foreach ($user->getClasses() as $classe) {
            foreach ($classe->getMatieres() as $matiere) {
                foreach ($matiere->getTasks() as $task) {
                    if ($task->getPriorite() === Task::PRIORITE_HIGH && $task->getStatut() === Task::STATUT_OPEN) {
                        $urgentTaskCount++;
                    }
                }
                foreach ($matiere->getExamens() as $exam) {
                    if ($exam->getDateExamen() > $now) {
                        $upcomingExamCount++;
                    }
                }
            }
        }

        return $this->render('student/student_settings.html.twig', [
            'student' => [
                'prenom'    => $user->getPrenom(),
                'nom'       => $user->getNom(),
                'email'     => $user->getEmail(),
                'studentId' => 'INSAT-' . str_pad((string)$user->getId(), 6, '0', STR_PAD_LEFT),
                'classe'    => implode(', ', array_values(array_filter(array_map(
                    fn ($classe) => $classe->getCode() ?: $classe->getNom(),
                    $user->getClasses()->toArray()
                )))) ?: null,
            ],
            // Notifications : à adapter si une entité Preferences est ajoutée
            'notifications' => [
                'examReminder'  => true,
                'taskAlert'     => true,
                'weeklySummary' => false,
            ],
            'urgentTaskCount'   => $urgentTaskCount,
            'upcomingExamCount' => $upcomingExamCount,
        ]);
    }
}
