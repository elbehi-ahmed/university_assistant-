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
class TeacherTasksController extends AbstractController
{
    #[Route('/tasks', name: 'app_teacher_tasks')]
    public function index(): Response
    {
        /** @var User $teacher */
        $teacher = $this->getUser();
        $now     = new \DateTime();

        $tasksData    = [];
        $classesSeen  = [];
        $classesList  = [];
        $openCount    = 0;
        $pendingCount = 0;

        foreach ($teacher->getMatieres() as $matiere) {
            $classe = $matiere->getClasse();
            if (!in_array($classe->getId(), $classesSeen)) {
                $classesSeen[] = $classe->getId();
                $classesList[] = ['value' => strtolower($classe->getCode()), 'code' => $classe->getCode()];
            }

            $totalStudents = $classe->getEtudiants()->count();

            foreach ($matiere->getTasks() as $task) {
                $submissions      = $task->getSubmissions();
                $submittedCount   = $submissions->filter(
                    fn($s) => $s->getStatut() !== Submission::STATUT_MISSING
                )->count();
                $submissionPercent = $totalStudents > 0
                    ? (int) round(($submittedCount / $totalStudents) * 100) : 0;

                $daysLeft = (int) $task->getDateLimite()->diff($now)->days;
                $isPast   = $task->getDateLimite() < $now;

                $deadlineClass = $isPast ? 'overdue' : ($daysLeft <= 3 ? 'soon' : 'ok');
                $deadlineStr   = $task->getDateLimite()->format('M j, Y');

                $submissionBarClass = $submissionPercent >= 80 ? 'success'
                    : ($submissionPercent < 40 ? 'danger' : '');

                if ($task->getStatut() === Task::STATUT_OPEN)   $openCount++;
                if ($task->getStatut() === Task::STATUT_REVIEW) $pendingCount++;

                $tasksData[] = [
                    'id'                  => $task->getId(),
                    'title'               => $task->getTitre(),
                    'subtitle'            => $task->getDescription() ?? '',
                    'classCode'           => $classe->getCode(),
                    'classBadgeClass'     => 'badge-gold',
                    'deadline'            => $deadlineStr,
                    'deadlineClass'       => $deadlineClass,
                    'maxPoints'           => $task->getPointsMax() ?? 20,
                    'priority'            => ucfirst($task->getPriorite()),
                    'priorityBadgeClass'  => match($task->getPriorite()) {
                        Task::PRIORITE_HIGH   => 'badge-danger',
                        Task::PRIORITE_MEDIUM => 'badge-warning',
                        default               => 'badge-success',
                    },
                    'submittedCount'      => $submittedCount,
                    'totalCount'          => $totalStudents,
                    'submissionPercent'   => $submissionPercent,
                    'submissionBarClass'  => $submissionBarClass,
                    'status'              => $task->getStatut(),
                    'statusLabel'         => match($task->getStatut()) {
                        Task::STATUT_OPEN   => '● Open',
                        Task::STATUT_REVIEW => '● Review',
                        default             => '● Closed',
                    },
                    'statusBadgeClass'    => match($task->getStatut()) {
                        Task::STATUT_OPEN   => 'badge-info',
                        Task::STATUT_REVIEW => 'badge-warning',
                        default             => 'badge-success',
                    },
                ];
            }
        }

        return $this->render('teacher/tasks.html.twig', [
            'tasks'               => $tasksData,
            'classesList'         => $classesList,
            'totalCount'          => count($tasksData),
            'openCount'           => $openCount,
            'pendingCount'        => $pendingCount,
            'activeClassCount'    => count($classesSeen),
            'openAssignmentCount' => $openCount,
        ]);
    }
}
