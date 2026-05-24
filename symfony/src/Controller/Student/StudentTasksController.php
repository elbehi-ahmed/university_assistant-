<?php

namespace App\Controller\Student;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_STUDENT')]
#[Route('/student')]
class StudentTasksController extends AbstractController
{
    #[Route('/tasks', name: 'app_student_tasks')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $now  = new \DateTime();

        $allTasks          = [];
        $urgentTaskCount   = 0;
        $upcomingExamCount = 0;

        foreach ($user->getClasses() as $classe) {
            foreach ($classe->getMatieres() as $matiere) {
                foreach ($matiere->getTasks() as $task) {
                    $daysLeft = (int) $task->getDateLimite()->diff($now)->days;
                    $isPast   = $task->getDateLimite() < $now;

                    // Deadline class for CSS coloring
                    if ($isPast || ($daysLeft <= 1 && !$isPast)) {
                        $deadlineClass = 'urgent';
                    } elseif ($daysLeft <= 3) {
                        $deadlineClass = 'soon';
                    } else {
                        $deadlineClass = '';
                    }

                    $allTasks[] = [
                        'id'           => $task->getId(),
                        'title'        => $task->getTitre(),
                        'description'  => $task->getDescription(),
                        'subject'      => $matiere->getNom(),
                        'deadline'     => $task->getDateLimite(),
                        'deadlineClass'=> $deadlineClass,
                        'priorite'     => $task->getPriorite(),
                        'barClass'     => match($task->getPriorite()) {
                            Task::PRIORITE_HIGH   => 'danger',
                            Task::PRIORITE_MEDIUM => 'warning',
                            default               => 'info',
                        },
                        'badgeClass'   => match($task->getPriorite()) {
                            Task::PRIORITE_HIGH   => 'badge-danger',
                            Task::PRIORITE_MEDIUM => 'badge-warning',
                            default               => 'badge-info',
                        },
                        'statut'       => $task->getStatut(),
                        'isDone'       => $task->getStatut() === Task::STATUT_CLOSED,
                    ];

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

        // Sort by deadline ascending
        usort($allTasks, fn($a, $b) => $a['deadline'] <=> $b['deadline']);

        $todoTasks = array_filter($allTasks, fn($t) => !$t['isDone']);
        $doneTasks = array_filter($allTasks, fn($t) => $t['isDone']);

        return $this->render('student/tasks.html.twig', [
            'tasks'             => array_values($allTasks),
            'todoCount'         => count($todoTasks),
            'doneCount'         => count($doneTasks),
            'totalCount'        => count($allTasks),
            'urgentTaskCount'   => $urgentTaskCount,
            'upcomingExamCount' => $upcomingExamCount,
        ]);
    }
}
