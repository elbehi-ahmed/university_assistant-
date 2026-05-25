<?php

namespace App\Controller\Teacher;

use App\Entity\Submission;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
#[Route('/teacher')]
class TeacherStudentsController extends AbstractController
{
    // Avatar gradient colors cycling per student
    private const GRADIENTS = [
        'linear-gradient(135deg, #2a6abf, #4c8fe0)',
        'linear-gradient(135deg, #8b2020, #e05c5c)',
        'linear-gradient(135deg, #2e8b57, #4caf82)',
        'linear-gradient(135deg, #7b3f9e, #b07be0)',
        'linear-gradient(135deg, #c9a84c, #e2c47a)',
    ];

    #[Route('/students', name: 'app_teacher_students')]
    public function index(): Response
    {
        /** @var User $teacher */
        $teacher = $this->getUser();

        $studentsSeen = [];
        $studentsData = [];
        $classesSeen  = [];
        $classesList  = [];
        $openCount    = 0;
        $colorIndex   = 0;

        foreach ($teacher->getMatieres() as $matiere) {
            $classe = $matiere->getClasse();
            if (!in_array($classe->getId(), $classesSeen)) {
                $classesSeen[] = $classe->getId();
                $classesList[] = ['code' => $classe->getCode()];
            }

            foreach ($matiere->getTasks() as $task) {
                if ($task->getStatut() === Task::STATUT_OPEN) $openCount++;
            }

            foreach ($classe->getEtudiants() as $student) {
                if (in_array($student->getId(), $studentsSeen)) {
                    continue;
                }
                $studentsSeen[] = $student->getId();

                // Calculate average grade from published grades
                $grades   = $student->getGrades()->filter(fn($g) => $g->isPublished())->toArray();
                $valeurs  = array_map(fn($g) => $g->getValeur(), $grades);
                $avg      = count($valeurs) > 0 ? round(array_sum($valeurs) / count($valeurs), 1) : 0.0;

                // Submission ratio for THIS teacher's tasks
                $totalTasks     = 0;
                $submittedCount = 0;
                foreach ($teacher->getMatieres() as $m) {
                    foreach ($m->getTasks() as $task) {
                        $totalTasks++;
                        foreach ($task->getSubmissions() as $sub) {
                            if ($sub->getStudent()->getId() === $student->getId()
                                && $sub->getStatut() !== Submission::STATUT_MISSING) {
                                $submittedCount++;
                            }
                        }
                    }
                }

                // Class tags for this student
                $classTags = [];
                foreach ($student->getClasses() as $c) {
                    $classTags[] = ['code' => $c->getCode(), 'badgeClass' => 'badge-gold'];
                }

                $studentsData[] = [
                    'id'          => $student->getId(),
                    'name'        => $student->getPrenom() . ' ' . $student->getNom(),
                    'initial'     => strtoupper(substr($student->getPrenom(), 0, 1)),
                    'avatarColor' => self::GRADIENTS[$colorIndex % count(self::GRADIENTS)],
                    'studentId'   => 'INSAT-' . str_pad((string)$student->getId(), 6, '0', STR_PAD_LEFT),
                    'isActive'    => false, // No live presence system yet
                    'classes'     => $classTags,
                    'average'     => $avg,
                    'avgClass'    => $avg >= 14 ? 'avg-high' : ($avg >= 10 ? 'avg-mid' : 'avg-low'),
                    'submitted'   => $submittedCount . '/' . $totalTasks,
                    'grade'       => $this->computeLettre($avg),
                    'gradeColor'  => $avg >= 14 ? '#6fd4a8' : ($avg >= 10 ? '#e0a84c' : '#e05c5c'),
                ];
                $colorIndex++;
            }
        }

        return $this->render('teacher/students.html.twig', [
            'students'            => $studentsData,
            'classesList'         => $classesList,
            'activeClassCount'    => count($classesSeen),
            'openAssignmentCount' => $openCount,
        ]);
    }

    private function computeLettre(float $val): string
    {
        return match(true) {
            $val >= 16 => 'A',
            $val >= 14 => 'B',
            $val >= 12 => 'C',
            $val >= 10 => 'D',
            default    => 'F',
        };
    }
}
