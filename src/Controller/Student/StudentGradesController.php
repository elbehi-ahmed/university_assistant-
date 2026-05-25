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
class StudentGradesController extends AbstractController
{
    #[Route('/grades', name: 'app_student_grades')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $now  = new \DateTime();

        $urgentTaskCount   = 0;
        $upcomingExamCount = 0;

        // ── Published grades — passed as entity objects so Twig can access
        //    grade.matiere.nom, grade.matiere.teacher.nom, grade.matiere.coefficient
        $grades    = [];
        $allValeurs = [];
        $nbEchecs   = 0;
        $totalCoefs = 0;
        $moyenneWeighted = 0;

        foreach ($user->getGrades() as $grade) {
            if (!$grade->isPublished()) {
                continue;
            }

            $valeur = $grade->getValeur();
            $coef   = $grade->getMatiere()->getCoefficient() ?? 1;

            $allValeurs[]      = $valeur;
            $moyenneWeighted  += $valeur * $coef;
            $totalCoefs       += $coef;

            if ($valeur < 10) {
                $nbEchecs++;
            }

            // Pass the entity directly — Twig uses grade.matiere.nom, grade.valeur, etc.
            $grades[] = $grade;
        }

        // ── Weighted average (by coefficient) ─────────────────
        $moyenneGenerale = $totalCoefs > 0
            ? round($moyenneWeighted / $totalCoefs, 2)
            : null;

        // ── Sidebar badges ─────────────────────────────────────
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

        return $this->render('student/grades.html.twig', [
            // Template variables expected by grades.html.twig
            'grades'             => $grades,           // Grade[] entities
            'moyenne_generale'   => $moyenneGenerale,  // float|null
            'total_credits'      => $totalCoefs,        // sum of coefficients
            'nb_echecs'          => $nbEchecs,          // int
            // sidebar badges
            'urgentTaskCount'    => $urgentTaskCount,
            'upcomingExamCount'  => $upcomingExamCount,
        ]);
    }
}