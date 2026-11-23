<?php

namespace App\Controller\Teacher;

use App\Entity\Classe;
use App\Entity\Matiere;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\ClasseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
#[Route('/teacher')]
class TeacherClassesController extends AbstractController
{
    #[Route('/classes/{id}/delete', name: 'app_teacher_classes_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        ClasseRepository $classeRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        /** @var User $teacher */
        $teacher = $this->getUser();

        $class = $classeRepository->find($id);

        if (!$class) {
            return new JsonResponse(['success' => false, 'message' => 'Class not found.'], Response::HTTP_NOT_FOUND);
        }

        $teacherOwnsClass = false;
        foreach ($class->getMatieres() as $matiere) {
            if ($matiere->getTeacher()?->getId() !== $teacher->getId()) {
                return new JsonResponse(['success' => false, 'message' => 'You cannot delete a shared class.'], Response::HTTP_FORBIDDEN);
            }

            $teacherOwnsClass = true;
        }

        if (!$teacherOwnsClass) {
            return new JsonResponse(['success' => false, 'message' => 'You cannot delete this class.'], Response::HTTP_FORBIDDEN);
        }

        if (!$this->isCsrfTokenValid('delete_class_' . $class->getId(), (string) $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid delete request.'], Response::HTTP_BAD_REQUEST);
        }

        $em->remove($class);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Class deleted successfully.']);
    }

    // Canonical category (value of the class form <select>) → category/icon
    private const CATEGORIES = [
        'math'    => ['category' => 'math',    'icon' => 'fa-square-root-alt'],
        'physics' => ['category' => 'physics', 'icon' => 'fa-atom'],
        'cs'      => ['category' => 'cs',      'icon' => 'fa-code'],
        'chem'    => ['category' => 'chem',    'icon' => 'fa-flask'],
        'english' => ['category' => 'english', 'icon' => 'fa-language'],
    ];

    // Keyword found in a free-text name → category/icon (matches banner CSS classes in classes.html.twig)
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

    #[Route('/classes', name: 'app_teacher_classes', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, ClasseRepository $classeRepository): Response
    {
        /** @var User $teacher */
        $teacher = $this->getUser();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('teacher_create_class', (string) $request->request->get('_token'))) {
                $this->addFlash('error', 'Invalid class creation request.');
                return $this->redirectToRoute('app_teacher_classes');
            }

            $name = trim((string) $request->request->get('name', ''));
            $rawCode = trim((string) $request->request->get('code', ''));
            $semester = trim((string) $request->request->get('semester', ''));
            $category = trim((string) $request->request->get('category', ''));
            $description = trim((string) $request->request->get('description', ''));
            $schedule = trim((string) $request->request->get('schedule', ''));
            $maxStudents = (int) $request->request->get('max_students', 0);

            $code = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $rawCode) ?: '');

            if ($code === '' && $name !== '') {
                $code = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $name) ?: '');
            }

            if ($name === '' || $code === '' || $semester === '' || $category === '') {
                $this->addFlash('error', 'Please fill in the class name, code, semester and category.');
                return $this->redirectToRoute('app_teacher_classes');
            }

            if ($classeRepository->findOneBy(['code' => $code])) {
                $this->addFlash('error', 'A class with this code already exists.');
                return $this->redirectToRoute('app_teacher_classes');
            }

            $class = new Classe();
            $class
                ->setNom($name)
                ->setCode($code)
                ->setNiveau($semester)
                ->setCategory($category)
                ->setDescription($description !== '' ? $description : null)
                ->setSchedule($schedule !== '' ? $schedule : null)
                ->setMaxStudents($maxStudents > 0 ? $maxStudents : null);

            $subject = new Matiere();
            $subject
                ->setNom($this->resolveCategoryLabel($category))
                ->setCoefficient(1.0)
                ->setIcone($this->resolveCategoryIcon($category))
                ->setCouleur($this->resolveCategoryColor($category))
                ->setTeacher($teacher)
                ->setClasse($class);

            $em->persist($class);
            $em->persist($subject);
            $em->flush();

            $this->addFlash('success', sprintf('Class %s was created successfully.', $class->getNom()));

            return $this->redirectToRoute('app_teacher_classes');
        }

        $classesSeen = [];
        $classesData = [];

        foreach ($teacher->getMatieres() as $matiere) {
            $classe = $matiere->getClasse();
            if (in_array($classe->getId(), $classesSeen, true)) {
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

            $catData = $this->resolveCategoryData($classe->getCategory() ?? $matiere->getNom());

            $classesData[] = [
                'id'                 => $classe->getId(),
                'name'               => $classe->getNom(),
                'code'               => $classe->getCode(),
                'semester'           => $classe->getNiveau(),
                'category'           => $catData['category'],
                'icon'               => $catData['icon'],
                'description'        => $classe->getDescription() ?? ('Classe ' . $classe->getNom()),
                'studentCount'       => $studentCount,
                'assignmentCount'    => $assignmentCount,
                'avgGrade'           => $avgGrade,
                'curriculumProgress' => min(100, (int) ($assignmentCount * 10)),
                'schedule'           => $classe->getSchedule() ?? 'See timetable',
            ];
        }

        return $this->render('teacher/classes.html.twig', [
            'classes'          => $classesData,
            'semesters'        => ['Semester 1', 'Semester 2', 'Semester 3', 'Semester 4'],
            'activeClassCount' => count($classesSeen),
            'openAssignmentCount' => array_sum(array_column($classesData, 'assignmentCount')),
        ]);
    }

    private function resolveCategoryData(string $nom): array
    {
        $lower = strtolower($nom);

        if (isset(self::CATEGORIES[$lower])) {
            return self::CATEGORIES[$lower];
        }

        foreach (self::CATEGORY_MAP as $keyword => $data) {
            if ($keyword !== 'default' && str_contains($lower, $keyword)) {
                return $data;
            }
        }
        return self::CATEGORY_MAP['default'];
    }

    private function resolveCategoryLabel(string $category): string
    {
        return match ($category) {
            'math' => 'Mathematics',
            'physics' => 'Physics',
            'cs' => 'Computer Science',
            'chem' => 'Chemistry',
            'english' => 'English',
            default => 'General Studies',
        };
    }

    private function resolveCategoryIcon(string $category): string
    {
        return self::CATEGORIES[$category]['icon'] ?? self::CATEGORY_MAP['default']['icon'];
    }

    private function resolveCategoryColor(string $category): ?string
    {
        return match ($category) {
            'math' => '38',
            'physics' => '214',
            'cs' => '145',
            'chem' => '34',
            'english' => '0',
            default => null,
        };
    }
}
