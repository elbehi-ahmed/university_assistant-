<?php

namespace App\Controller\Teacher;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
#[Route('/teacher')]
class TeacherSettingsController extends AbstractController
{
    #[Route('/settings', name: 'app_teacher_settings', methods: ['GET', 'POST'])]
    public function index(
        Request                     $request,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $hasher,
        UserRepository              $users
    ): Response {
        /** @var User $teacher */
        $teacher = $this->getUser();

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');

            if ($action === 'profile') {
                $prenom = trim($request->request->get('prenom', ''));
                $nom    = trim($request->request->get('nom', ''));
                $email  = strtolower(trim($request->request->get('email', '')));
                $owner  = $email !== '' ? $users->findOneBy(['email' => $email]) : null;

                if (!$prenom || !$nom || !$email) {
                    $this->addFlash('error', 'All fields are required.');
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->addFlash('error', 'Invalid email address.');
                } elseif ($owner && $owner->getId() !== $teacher->getId()) {
                    $this->addFlash('error', 'This email address is already used by another account.');
                } else {
                    $teacher->setPrenom($prenom);
                    $teacher->setNom($nom);
                    $teacher->setEmail($email);
                    $em->flush();
                    $this->addFlash('success', 'Profile updated successfully.');
                }
            }

            if ($action === 'password') {
                $current = $request->request->get('current_password', '');
                $new     = $request->request->get('new_password', '');
                $confirm = $request->request->get('confirm_password', '');

                if (!$hasher->isPasswordValid($teacher, $current)) {
                    $this->addFlash('error', 'Current password is incorrect.');
                } elseif (strlen($new) < 8) {
                    $this->addFlash('error', 'New password must be at least 8 characters.');
                } elseif ($new !== $confirm) {
                    $this->addFlash('error', 'Passwords do not match.');
                } else {
                    $teacher->setPassword($hasher->hashPassword($teacher, $new));
                    $em->flush();
                    $this->addFlash('success', 'Password updated successfully.');
                }
            }

            return $this->redirectToRoute('app_teacher_settings');
        }

        // ── Sidebar badges ────────────────────────────────────
        $classesSeen = [];
        $openCount   = 0;
        foreach ($teacher->getMatieres() as $matiere) {
            $id = $matiere->getClasse()->getId();
            if (!in_array($id, $classesSeen)) $classesSeen[] = $id;
            foreach ($matiere->getTasks() as $task) {
                if ($task->getStatut() === Task::STATUT_OPEN) $openCount++;
            }
        }

        return $this->render('teacher/settings.html.twig', [
            'teacher' => [
                'prenom'     => $teacher->getPrenom(),
                'nom'        => $teacher->getNom(),
                'email'      => $teacher->getEmail(),
                'department' => 'Computer Science', // à personnaliser si champ ajouté à User
                'initials'   => strtoupper(substr($teacher->getPrenom(), 0, 1) . substr($teacher->getNom(), 0, 1)),
            ],
            'departments'   => ['Computer Science', 'Mathematics', 'Physics', 'Electronics', 'Networks'],
            'gradingScales' => ['Percentage (0-100)', 'Letter Grade (A-F)', 'Points (0-20)'],
            'notifications' => [
                'submissionAlerts' => true,
                'examReminders'    => true,
                'weeklySummary'    => false,
            ],
            'preferences' => [
                'defaultGradingScale'    => 'Percentage (0-100)',
                'autoCalculateAverage'   => true,
            ],
            'activeClassCount'    => count($classesSeen),
            'openAssignmentCount' => $openCount,
        ]);
    }
}
