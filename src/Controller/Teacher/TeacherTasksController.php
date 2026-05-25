<?php

namespace App\Controller\Teacher;

use App\Entity\Submission;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
#[Route('/teacher')]
class TeacherTasksController extends AbstractController
{
    #[Route('/tasks/{id}/delete', name: 'app_teacher_tasks_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        TaskRepository $taskRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        /** @var User $teacher */
        $teacher = $this->getUser();

        $task = $taskRepository->find($id);

        if (!$task) {
            return new JsonResponse(['success' => false, 'message' => 'Task not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($task->getMatiere()?->getTeacher()?->getId() !== $teacher->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'You cannot delete this task.'], Response::HTTP_FORBIDDEN);
        }

        if (!$this->isCsrfTokenValid('delete_task_' . $task->getId(), (string) $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid delete request.'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Task deleted successfully.']);
    }

    #[Route('/tasks', name: 'app_teacher_tasks', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $teacher */
        $teacher = $this->getUser();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('teacher_create_task', (string) $request->request->get('_token'))) {
                $this->addFlash('error', 'Invalid task creation request.');

                return $this->redirectToRoute('app_teacher_tasks');
            }

            $title = trim((string) $request->request->get('title', ''));
            $description = trim((string) $request->request->get('description', ''));
            $priority = strtolower(trim((string) $request->request->get('priority', Task::PRIORITE_MEDIUM)));
            $deadlineInput = trim((string) $request->request->get('deadline', ''));
            $classId = (int) $request->request->get('class_id', 0);
            $maxPoints = (int) $request->request->get('max_points', 0);

            if ($title === '' || $deadlineInput === '' || $classId <= 0) {
                $this->addFlash('error', 'Please fill in the task title, class and deadline.');

                return $this->redirectToRoute('app_teacher_tasks');
            }

            if (!in_array($priority, [Task::PRIORITE_HIGH, Task::PRIORITE_MEDIUM, Task::PRIORITE_LOW], true)) {
                $priority = Task::PRIORITE_MEDIUM;
            }

            $deadline = \DateTimeImmutable::createFromFormat('Y-m-d', $deadlineInput);
            if (!$deadline instanceof \DateTimeImmutable) {
                $this->addFlash('error', 'Please provide a valid deadline.');

                return $this->redirectToRoute('app_teacher_tasks');
            }

            $deadline = $deadline->setTime(23, 59, 59);

            $matiere = null;
            foreach ($teacher->getMatieres() as $ownedMatiere) {
                $classe = $ownedMatiere->getClasse();
                if ($classe !== null && $classe->getId() === $classId) {
                    $matiere = $ownedMatiere;
                    break;
                }
            }

            if ($matiere === null) {
                $this->addFlash('error', 'You can only create tasks for one of your classes.');

                return $this->redirectToRoute('app_teacher_tasks');
            }

            $task = new Task();
            $task
                ->setTitre($title)
                ->setDescription($description !== '' ? $description : null)
                ->setDateLimite($deadline)
                ->setPriorite($priority)
                ->setStatut(Task::STATUT_OPEN)
                ->setPointsMax($maxPoints > 0 ? $maxPoints : null)
                ->setMatiere($matiere);

            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Task "%s" was created successfully.', $title));

            return $this->redirectToRoute('app_teacher_tasks');
        }

        $now = new \DateTime();
        $tasksData = [];
        $classesSeen = [];
        $classesList = [];
        $openCount = 0;
        $pendingCount = 0;

        foreach ($teacher->getMatieres() as $matiere) {
            $classe = $matiere->getClasse();
            if ($classe === null || in_array($classe->getId(), $classesSeen, true)) {
                continue;
            }

            $classesSeen[] = $classe->getId();
            $classesList[] = [
                'id' => $classe->getId(),
                'value' => strtolower($classe->getCode()),
                'code' => $classe->getCode(),
            ];

            $totalStudents = $classe->getEtudiants()->count();

            foreach ($matiere->getTasks() as $task) {
                $submissions = $task->getSubmissions();
                $submittedCount = $submissions->filter(
                    fn ($submission) => $submission->getStatut() !== Submission::STATUT_MISSING
                )->count();
                $submissionPercent = $totalStudents > 0
                    ? (int) round(($submittedCount / $totalStudents) * 100)
                    : 0;

                $daysLeft = (int) $task->getDateLimite()->diff($now)->days;
                $isPast = $task->getDateLimite() < $now;

                $deadlineClass = $isPast ? 'overdue' : ($daysLeft <= 3 ? 'soon' : 'ok');
                $deadlineStr = $task->getDateLimite()->format('M j, Y');

                $submissionBarClass = $submissionPercent >= 80 ? 'success'
                    : ($submissionPercent < 40 ? 'danger' : '');

                if ($task->getStatut() === Task::STATUT_OPEN) {
                    $openCount++;
                }

                if ($task->getStatut() === Task::STATUT_REVIEW) {
                    $pendingCount++;
                }

                $tasksData[] = [
                    'id' => $task->getId(),
                    'title' => $task->getTitre(),
                    'subtitle' => $task->getDescription() ?? '',
                    'classCode' => $classe->getCode(),
                    'classBadgeClass' => 'badge-gold',
                    'deadline' => $deadlineStr,
                    'deadlineClass' => $deadlineClass,
                    'maxPoints' => $task->getPointsMax() ?? 20,
                    'priority' => ucfirst($task->getPriorite()),
                    'priorityBadgeClass' => match ($task->getPriorite()) {
                        Task::PRIORITE_HIGH => 'badge-danger',
                        Task::PRIORITE_MEDIUM => 'badge-warning',
                        default => 'badge-success',
                    },
                    'submittedCount' => $submittedCount,
                    'totalCount' => $totalStudents,
                    'submissionPercent' => $submissionPercent,
                    'submissionBarClass' => $submissionBarClass,
                    'status' => $task->getStatut(),
                    'statusLabel' => match ($task->getStatut()) {
                        Task::STATUT_OPEN => '● Open',
                        Task::STATUT_REVIEW => '● Review',
                        default => '● Closed',
                    },
                    'statusBadgeClass' => match ($task->getStatut()) {
                        Task::STATUT_OPEN => 'badge-info',
                        Task::STATUT_REVIEW => 'badge-warning',
                        default => 'badge-success',
                    },
                ];
            }
        }

        return $this->render('teacher/tasks.html.twig', [
            'tasks' => $tasksData,
            'classesList' => $classesList,
            'totalCount' => count($tasksData),
            'openCount' => $openCount,
            'pendingCount' => $pendingCount,
            'activeClassCount' => count($classesSeen),
            'openAssignmentCount' => $openCount,
        ]);
    }
}