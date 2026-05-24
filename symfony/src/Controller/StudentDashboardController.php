<?php

namespace App\Controller\Student;

use App\Entity\Examen;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\ExamenRepository;
use App\Repository\MatiereRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_STUDENT')]
#[Route('/student')]
class StudentDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_student_dashboard')]
    public function index(
        MatiereRepository $matiereRepo,
        TaskRepository    $taskRepo,
        ExamenRepository  $examenRepo
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $now  = new \DateTime();

        // ── Subjects ──────────────────────────────────────────
        $subjects = [];
        foreach ($user->getClasses() as $classe) {
            foreach ($classe->getMatieres() as $matiere) {
                $subjects[] = $matiere;
            }
        }

        // ── All tasks + urgent tasks ───────────────────────────
        $allTasks    = [];
        $urgentTasks = [];
        foreach ($subjects as $matiere) {
            foreach ($matiere->getTasks() as $task) {
                $allTasks[] = $task;
                if ($task->getPriorite() === Task::PRIORITE_HIGH && $task->getStatut() === Task::STATUT_OPEN) {
                    $urgentTasks[] = $task;
                }
            }
        }

        usort($urgentTasks, fn($a, $b) => $a->getDateLimite() <=> $b->getDateLimite());
        $urgentTasks = array_slice($urgentTasks, 0, 3);

        $doneTasks = array_filter($allTasks, fn($t) => $t->getStatut() === Task::STATUT_CLOSED);

        // ── Upcoming exams ─────────────────────────────────────
        $allExamens = [];
        foreach ($subjects as $matiere) {
            foreach ($matiere->getExamens() as $exam) {
                $allExamens[] = $exam;
            }
        }
        $upcomingExamsList = array_filter($allExamens, fn($e) => $e->getDateExamen() > $now);
        usort($upcomingExamsList, fn($a, $b) => $a->getDateExamen() <=> $b->getDateExamen());

        // Days until next exam
        $nextExam         = !empty($upcomingExamsList) ? array_values($upcomingExamsList)[0] : null;
        $daysUntilNextExam = $nextExam
            ? (int) $nextExam->getDateExamen()->diff($now)->days
            : 0;

        // ── Build `tasks` array — shape expected by student/dashboard.html.twig ──
        // template uses: task.title, task.priority, task.subject.name,
        //                task.dueLabel, task.progressPercent
        $urgentTaskCount = count(array_filter($allTasks, fn($t) =>
            $t->getPriorite() === Task::PRIORITE_HIGH && $t->getStatut() === Task::STATUT_OPEN
        ));

        $tasksForView = array_map(function (Task $task) use ($now) {
            $days   = (int) $task->getDateLimite()->diff($now)->days;
            $isPast = $task->getDateLimite() < $now;

            if ($isPast)        { $dueLabel = 'Overdue!'; }
            elseif ($days === 0){ $dueLabel = 'Due today'; }
            elseif ($days === 1){ $dueLabel = 'Due tomorrow'; }
            else                { $dueLabel = 'Due in ' . $days . ' days'; }

            return [
                'title'           => $task->getTitre(),
                'priority'        => $task->getPriorite(),       // 'high'|'medium'|'low'
                'subject'         => ['name' => $task->getMatiere()->getNom()],
                'dueLabel'        => $dueLabel,
                'progressPercent' => 0, // no submission tracking per-student yet
            ];
        }, $urgentTasks);

        // ── Build `exams` array — shape expected by student/dashboard.html.twig ──
        // template uses: exam.examDate (DateTime), exam.subject.name,
        //                exam.room, exam.time, exam.daysUntil, exam.urgencyBadgeClass
        $examsForView = array_map(function (Examen $exam) use ($now) {
            $days = (int) $exam->getDateExamen()->diff($now)->days;
            return [
                'examDate'          => $exam->getDateExamen(),
                'subject'           => ['name' => $exam->getMatiere()->getNom()],
                'room'              => $exam->getSalle(),
                'time'              => $exam->getDateExamen()->format('H:i'),
                'daysUntil'         => $days,
                'urgencyBadgeClass' => $days <= 7 ? 'badge-danger' : 'badge-warning',
            ];
        }, array_slice(array_values($upcomingExamsList), 0, 2));

        // ── Build `subjects` array — shape expected by student/dashboard.html.twig ──
        // template uses: subject.name, subject.progressPercent, subject.progressBarClass
        $subjectsForView = array_map(function ($matiere) {
            $tasks  = $matiere->getTasks()->toArray();
            $total  = count($tasks);
            $done   = count(array_filter($tasks, fn($t) => $t->getStatut() === Task::STATUT_CLOSED));
            $pct    = $total > 0 ? (int) round(($done / $total) * 100) : 0;
            return [
                'name'             => $matiere->getNom(),
                'progressPercent'  => $pct,
                'progressBarClass' => $pct >= 75 ? 'success' : ($pct < 40 ? 'danger' : ''),
            ];
        }, $subjects);

        // ── `stats` object — shape expected by student/dashboard.html.twig ──
        $stats = [
            'subjectCount'      => count($subjects),
            'urgentTaskCount'   => $urgentTaskCount,
            'doneTaskCount'     => count($doneTasks),
            'upcomingExamCount' => count($upcomingExamsList),
            'daysUntilNextExam' => $daysUntilNextExam,
        ];

        // ── Sidebar badges (used by base.html.twig) ───────────
        $upcomingExamCount = count($upcomingExamsList);

        return $this->render('student/dashboard.html.twig', [
            'stats'             => $stats,
            'tasks'             => $tasksForView,
            'exams'             => $examsForView,
            'subjects'          => $subjectsForView,
            // sidebar badges
            'urgentTaskCount'   => $urgentTaskCount,
            'upcomingExamCount' => $upcomingExamCount,
        ]);
    }
}