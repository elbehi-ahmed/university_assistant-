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
class TeacherClassesController extends AbstractController
{
    // Map keyword → category/icon (matches banner CSS classes in classes.html.twig)
    private const CATEGORY_MAP = [
        'math'    => ['category' => 'math',    'icon' => 'fa-square-root-alt'],
        'physi'   => ['category' => 'physics', 'icon' => 'fa-atom'],
        'algo'    => ['category' => 'cs',      'icon' => 'fa-code'],
        'network' => ['category' => 'cs',      'icon' => 'fa-network-wired'],
        'database'=> ['category' => 'cs',      'icon' => 'fa-database'],
        'chimie'  => ['category' => 'chem',    'icon' => 'fa-flask'],
        'english' => ['category' => 'english', 'icon' => 'fa-language'],
        'default' => ['category' => 'cs',      'icon' => 'fa-book'],
    ];

    #[Route('/classes', name: 'app_teacher_classes')]
    public function index(): Response
    {
        /** @var User $teacher */
        $teacher = $this->getUser();

        $classesSeen = [];
        $classesData = [];

        foreach ($teacher->getMatieres() as $matiere) {
            $classe = $matiere->getClasse();
            if (in_array($classe->getId(), $classesSeen)) {
                continue;
            }
            $classesSeen[] = $classe->getId();

            // Calculate stats
            $studentCount    = $classe->getEtudiants()->count();
            $assignmentCount = 0;
            $allGrades       = [];

            foreach ($classe->getMatieres() as $m) {
                $assignmentCount += $m->getTasks()->count();
                foreach ($m->getGrades() as $grade) {
                    if ($grade->isPublished()) {
                        $allGrades[] = $grade->getValeur();
                    }
                }
            }

            $avgGrade = count($allGrades) > 0
                ? round(array_sum($allGrades) / count($allGrades), 1)
                : 0.0;

            $catData = $this->resolveCategory($matiere->getNom());

            $classesData[] = [
                'id'                 => $classe->getId(),
                'name'               => $classe->getNom(),
                'code'               => $classe->getCode(),
                'semester'           => $classe->getNiveau(),
                'category'           => $catData['category'],
                'icon'               => $catData['icon'],
                'description'        => 'Classe ' . $classe->getNom(),
                'studentCount'       => $studentCount,
                'assignmentCount'    => $assignmentCount,
                'avgGrade'           => $avgGrade,
                'curriculumProgress' => min(100, (int) ($assignmentCount * 10)),
                'schedule'           => 'See timetable',
            ];
        }

        return $this->render('teacher/classes.html.twig', [
            'classes'          => $classesData,
            'semesters'        => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'],
            'activeClassCount' => count($classesSeen),
            'openAssignmentCount' => array_sum(array_column($classesData, 'assignmentCount')),
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
