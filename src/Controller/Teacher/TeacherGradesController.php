<?php

namespace App\Controller\Teacher;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
#[Route('/teacher')]
class TeacherGradesController extends AbstractController
{
    #[Route('/grades', name: 'app_teacher_grades')]
    public function index(Request $request): Response
    {
        /** @var User $teacher */
        $teacher = $this->getUser();

        $classesSeen  = [];
        $classesList  = []; // for <select> filter: [id, code, name]
        $openCount    = 0;

        // ── Build classes list ─────────────────────────────────
        foreach ($teacher->getMatieres() as $matiere) {
            $classe = $matiere->getClasse();
            if (!in_array($classe->getId(), $classesSeen)) {
                $classesSeen[] = $classe->getId();
                $classesList[] = [
                    'id'   => $classe->getId(),
                    'code' => $classe->getCode(),
                    'name' => $classe->getNom(),
                ];
            }
            foreach ($matiere->getTasks() as $task) {
                if ($task->getStatut() === Task::STATUT_OPEN) $openCount++;
            }
        }

        // ── Selected class (from query param or first available) ──
        $selectedClassId = $request->query->getInt('class_id',
            !empty($classesList) ? $classesList[0]['id'] : 0
        );
        $selectedClass = null;
        foreach ($classesList as $c) {
            if ($c['id'] === $selectedClassId) {
                $selectedClass = $c;
                break;
            }
        }

        // ── Assignments for selected class ─────────────────────
        $assignments = [];
        foreach ($teacher->getMatieres() as $matiere) {
            if ($matiere->getClasse()->getId() !== $selectedClassId) continue;
            foreach ($matiere->getTasks() as $task) {
                $assignments[] = [
                    'id'           => $task->getId(),
                    'title'        => $task->getTitre(),
                    'maxPoints'    => $task->getPointsMax() ?? 20,
                    'studentCount' => $matiere->getClasse()->getEtudiants()->count(),
                ];
            }
        }

        // ── Selected assignment ────────────────────────────────
        $selectedTaskId  = $request->query->getInt('task_id',
            !empty($assignments) ? $assignments[0]['id'] : 0
        );
        $selectedAssignment = null;
        foreach ($assignments as $a) {
            if ($a['id'] === $selectedTaskId) {
                $selectedAssignment = $a;
                break;
            }
        }

        // ── Grades for selected assignment ─────────────────────
        // template uses: grade.studentName, grade.studentInitial,
        //                grade.points, grade.letterGrade, grade.feedback, grade.id
        $gradesForView = [];
        $allValeurs    = [];

        foreach ($teacher->getMatieres() as $matiere) {
            if ($matiere->getClasse()->getId() !== $selectedClassId) continue;
            foreach ($matiere->getGrades() as $grade) {
                $submission = $grade->getSubmission();
                if ($submission === null) continue;
                if ($submission->getTask()->getId() !== $selectedTaskId) continue;

                $valeur = $grade->getValeur();
                $allValeurs[] = $valeur;

                $gradesForView[] = [
                    'id'            => $grade->getId(),
                    'studentName'   => $grade->getStudent()->getPrenom() . ' ' . $grade->getStudent()->getNom(),
                    'studentInitial'=> strtoupper(substr($grade->getStudent()->getPrenom(), 0, 1)),
                    'points'        => $valeur,
                    'letterGrade'   => $grade->getLettre() ?? $this->computeLettre($valeur),
                    'feedback'      => $grade->getCommentaire(),
                ];
            }
        }

        // ── Stats for selected assignment ──────────────────────
        $totalStudents = $selectedClass !== null
            ? (function () use ($teacher, $selectedClassId) {
                foreach ($teacher->getMatieres() as $m) {
                    if ($m->getClasse()->getId() === $selectedClassId) {
                        return $m->getClasse()->getEtudiants()->count();
                    }
                }
                return 0;
              })()
            : 0;

        $stats = [
            'avg'     => count($allValeurs) > 0 ? round(array_sum($allValeurs) / count($allValeurs), 1) : '—',
            'highest' => count($allValeurs) > 0 ? max($allValeurs) : '—',
            'lowest'  => count($allValeurs) > 0 ? min($allValeurs) : '—',
            'graded'  => count($allValeurs),
            'total'   => $totalStudents,
        ];

        return $this->render('teacher/grades.html.twig', [
            'classes'             => $classesList,
            'selectedClass'       => $selectedClass,
            'assignments'         => $assignments,
            'selectedAssignment'  => $selectedAssignment,
            'grades'              => $gradesForView,
            'stats'               => $stats,
            // sidebar badges
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