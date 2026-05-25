<?php

namespace App\Controller\Teacher;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
#[Route('/teacher')]
class TeacherDashboardController extends AbstractController
{
    // Keyword → icon/category map (same as TeacherClassesController)
    private const CATEGORY_MAP = [
        'math'     => ['category' => 'math',    'icon' => 'fa-square-root-alt'],
        'physi'    => ['category' => 'physics',  'icon' => 'fa-atom'],
        'algo'     => ['category' => 'cs',       'icon' => 'fa-code'],
        'network'  => ['category' => 'cs',       'icon' => 'fa-network-wired'],
        'database' => ['category' => 'cs',       'icon' => 'fa-database'],
        'chimie'   => ['category' => 'chem',     'icon' => 'fa-flask'],
        'english'  => ['category' => 'english',  'icon' => 'fa-language'],
        'default'  => ['category' => 'cs',       'icon' => 'fa-book'],
    ];

    #[Route('/dashboard', name: 'app_teacher_dashboard')]
    public function index(): Response
    {
        /** @var User $teacher */
        $teacher = $this->getUser();
        $now     = new \DateTime();

        $classesSeen   = [];
        $totalStudents = 0;
        $openTasks     = 0;
        $allTasks      = 0;
        $submittedCount = 0;

        // ── Gather class/task stats ────────────────────────────
        foreach ($teacher->getMatieres() as $matiere) {
            $classe = $matiere->getClasse();
            if (!in_array($classe->getId(), $classesSeen)) {
                $classesSeen[]  = $classe->getId();
                $totalStudents += $classe->getEtudiants()->count();
            }
            foreach ($matiere->getTasks() as $task) {
                $allTasks++;
                if ($task->getStatut() === Task::STATUT_OPEN) {
                    $openTasks++;
                }
                // Count non-missing submissions for submission rate
                foreach ($task->getSubmissions() as $sub) {
                    if ($sub->getStatut() !== 'missing') {
                        $submittedCount++;
                    }
                }
            }
        }

        // Submission rate = submitted / (total tasks × total students) × 100
        $submissionRate = ($allTasks > 0 && $totalStudents > 0)
            ? (int) round(($submittedCount / ($allTasks * $totalStudents)) * 100)
            : 0;

        // ── Upcoming exams ─────────────────────────────────────
        $upcomingExams    = [];
        $upcomingExamCount = 0;
        foreach ($teacher->getMatieres() as $matiere) {
            foreach ($matiere->getExamens() as $exam) {
                if ($exam->getDateExamen() > $now) {
                    $upcomingExamCount++;
                    $upcomingExams[] = [
                        'subject'   => $matiere->getNom(),
                        'date'      => $exam->getDateExamen(),
                        'room'      => $exam->getSalle(),
                        'startTime' => $exam->getDateExamen()->format('H:i'),
                    ];
                }
            }
        }
        usort($upcomingExams, fn($a, $b) => $a['date'] <=> $b['date']);
        $upcomingExams = array_slice($upcomingExams, 0, 3);

        // ── Build `stats` array — shape expected by teacher/dashboard.html.twig ──
        $stats = [
            'activeClassCount'  => count($classesSeen),
            'totalStudents'     => $totalStudents,
            'openAssignments'   => $openTasks,
            'submissionRate'    => $submissionRate,
            'upcomingExamCount' => $upcomingExamCount,
        ];

        // ── Build `classes` array — shape expected by teacher/dashboard.html.twig ──
        // template uses: class.iconClass, class.iconFaClass, class.name, class.code,
        //                class.studentCount, class.taskCount,
        //                class.curriculumProgress, class.progressBarClass
        $classesSeen2 = [];
        $classesForView = [];
        foreach ($teacher->getMatieres() as $matiere) {
            $classe = $matiere->getClasse();
            if (in_array($classe->getId(), $classesSeen2)) {
                continue;
            }
            $classesSeen2[] = $classe->getId();

            $taskCount  = 0;
            $openCount  = 0;
            foreach ($classe->getMatieres() as $m) {
                $taskCount += $m->getTasks()->count();
                foreach ($m->getTasks() as $t) {
                    if ($t->getStatut() === Task::STATUT_OPEN) $openCount++;
                }
            }

            $progress = $taskCount > 0
                ? (int) round((($taskCount - $openCount) / $taskCount) * 100)
                : 0;

            $catData = $this->resolveCategory($matiere->getNom());

            $classesForView[] = [
                'iconClass'          => $catData['category'],   // 'math'|'physics'|'cs'…
                'iconFaClass'        => $catData['icon'],        // 'fa-square-root-alt'…
                'name'               => $classe->getNom(),
                'code'               => $classe->getCode() . ' · ' . $classe->getNiveau(),
                'studentCount'       => $classe->getEtudiants()->count(),
                'taskCount'          => $taskCount,
                'curriculumProgress' => $progress,
                'progressBarClass'   => $progress >= 75 ? 'success' : ($progress < 40 ? 'danger' : ''),
            ];

            if (count($classesForView) >= 3) break; // dashboard shows max 3
        }

        return $this->render('teacher/dashboard.html.twig', [
            'stats'               => $stats,
            'classes'             => $classesForView,
            'recentActivity'      => [], // TODO: implement activity log
            // sidebar badges
            'activeClassCount'    => count($classesSeen),
            'openAssignmentCount' => $openTasks,
        ]);
    }

    private function resolveCategory(string $nom): array
    {
        $lower = strtolower($nom);
        foreach (self::CATEGORY_MAP as $keyword => $data) {
            if ($keyword !== 'default' && str_contains($lower, $keyword)) {
                return $data;
            }
        }
        return self::CATEGORY_MAP['default'];
    }
}