<?php

namespace App\Controller\Student;

use App\Entity\Examen;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_STUDENT')]
#[Route('/student')]
class StudentExamsController extends AbstractController
{
    #[Route('/exams', name: 'app_student_exams')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $now  = new \DateTime();

        $allExamens        = [];
        $subjects          = [];
        $urgentTaskCount   = 0;
        $upcomingExamCount = 0;

        foreach ($user->getClasses() as $classe) {
            foreach ($classe->getMatieres() as $matiere) {
                $subjects[] = ['id' => $matiere->getId(), 'name' => $matiere->getNom()];

                foreach ($matiere->getTasks() as $task) {
                    if ($task->getPriorite() === Task::PRIORITE_HIGH && $task->getStatut() === Task::STATUT_OPEN) {
                        $urgentTaskCount++;
                    }
                }
                foreach ($matiere->getExamens() as $exam) {
                    $allExamens[] = $exam;
                    if ($exam->getDateExamen() > $now) {
                        $upcomingExamCount++;
                    }
                }
            }
        }

        // ── Séparer upcoming / past ───────────────────────────
        $upcoming = array_filter($allExamens, fn($e) => $e->getDateExamen() > $now);
        $past     = array_filter($allExamens, fn($e) => $e->getDateExamen() <= $now);

        usort($upcoming, fn($a, $b) => $a->getDateExamen() <=> $b->getDateExamen());
        usort($past,     fn($a, $b) => $b->getDateExamen() <=> $a->getDateExamen());

        $upcomingExamsData = array_map(fn($e) => $this->formatExam($e, $now, false), array_values($upcoming));
        $pastExamsData     = array_map(fn($e) => $this->formatExam($e, $now, true),  array_values($past));

        // ── Prochain examen (countdown) ───────────────────────
        $nextExamData = null;
        if (!empty($upcomingExamsData)) {
            $nextExamData = $upcomingExamsData[0];
        }

        return $this->render('student/exams.html.twig', [
            'upcomingExams'     => $upcomingExamsData,
            'pastExams'         => $pastExamsData,
            'nextExam'          => $nextExamData,
            'subjects'          => $subjects,
            'urgentTaskCount'   => $urgentTaskCount,
            'upcomingExamCount' => $upcomingExamCount,
        ]);
    }

    // ── Enrichissement d'un examen pour la vue ────────────────
    private function formatExam(Examen $exam, \DateTime $now, bool $isPast): array
    {
        $days = (int) abs($exam->getDateExamen()->diff($now)->days);

        if ($isPast) {
            $countdownClass     = 'past-label';
            $countdownIconClass = 'fa-check-circle';
            $countdownLabel     = 'Completed';
            $urgencyBadgeClass  = 'badge-muted';
            $urgencyBadgeLabel  = 'Past';
        } elseif ($days <= 7) {
            $countdownClass     = '';          // rouge
            $countdownIconClass = 'fa-fire';
            $countdownLabel     = 'In ' . $days . ' day' . ($days > 1 ? 's' : '') . ' — prepare now!';
            $urgencyBadgeClass  = 'badge-danger';
            $urgencyBadgeLabel  = 'Urgent';
        } else {
            $countdownClass     = 'moderate';  // orange
            $countdownIconClass = 'fa-calendar';
            $countdownLabel     = 'In ' . $days . ' days';
            $urgencyBadgeClass  = 'badge-warning';
            $urgencyBadgeLabel  = 'Soon';
        }

        return [
            'subject'            => ['name' => $exam->getMatiere()->getNom()],
            'examDate'           => $exam->getDateExamen(),
            'room'               => $exam->getSalle(),
            'startTime'          => $exam->getDateExamen()->format('H:i'),
            'endTime'            => $exam->getHeureFin()->format('H:i'),
            'duration'           => $exam->getDureeMinutes() . ' min',
            'countdownClass'     => $countdownClass,
            'countdownIconClass' => $countdownIconClass,
            'countdownLabel'     => $countdownLabel,
            'urgencyBadgeClass'  => $urgencyBadgeClass,
            'urgencyBadgeLabel'  => $urgencyBadgeLabel,
        ];
    }
}
