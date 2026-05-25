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
class StudentSubjectsController extends AbstractController
{
    // Map entre nom de matière et icône FontAwesome + teinte HSL
    // Extensible selon les matières réelles en base
    private const ICON_MAP = [
        'math'       => ['icon' => 'fa-square-root-alt', 'hue' => 38],
        'physi'      => ['icon' => 'fa-atom',            'hue' => 200],
        'english'    => ['icon' => 'fa-language',        'hue' => 140],
        'algo'       => ['icon' => 'fa-code',            'hue' => 280],
        'database'   => ['icon' => 'fa-database',        'hue' => 170],
        'network'    => ['icon' => 'fa-network-wired',   'hue' => 10],
        'chimie'     => ['icon' => 'fa-flask',           'hue' => 300],
        'electric'   => ['icon' => 'fa-bolt',            'hue' => 55],
        'mecani'     => ['icon' => 'fa-cogs',            'hue' => 220],
        'default'    => ['icon' => 'fa-book',            'hue' => 38],
    ];

    #[Route('/subjects', name: 'app_student_subjects')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $subjects = [];
        $urgentTaskCount   = 0;
        $upcomingExamCount = 0;
        $now = new \DateTime();

        foreach ($user->getClasses() as $classe) {
            foreach ($classe->getMatieres() as $matiere) {
                $taskCount  = $matiere->getTasks()->count();
                $examCount  = $matiere->getExamens()->count();

                // Badges sidebar
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

                // Icône et teinte selon le nom de la matière
                $iconData = $this->resolveIcon($matiere->getNom());

                $subjects[] = [
                    'id'          => $matiere->getId(),
                    'name'        => $matiere->getNom(),
                    'icon'        => $iconData['icon'],
                    'hue'         => $iconData['hue'],
                    'coefficient' => $matiere->getCoefficient(),
                    'taskCount'   => $taskCount,
                    'examCount'   => $examCount,
                ];
            }
        }

        return $this->render('student/matieres.html.twig', [
            'subjects'          => $subjects,
            'urgentTaskCount'   => $urgentTaskCount,
            'upcomingExamCount' => $upcomingExamCount,
        ]);
    }

    private function resolveIcon(string $nom): array
    {
        $lower = strtolower($nom);
        foreach (self::ICON_MAP as $keyword => $data) {
            if ($keyword !== 'default' && str_contains($lower, $keyword)) {
                return $data;
            }
        }
        return self::ICON_MAP['default'];
    }
}
