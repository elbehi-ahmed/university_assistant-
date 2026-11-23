<?php

namespace App\Command;

use App\Entity\AllowedEmail;
use App\Entity\Classe;
use App\Entity\Examen;
use App\Entity\Grade;
use App\Entity\Matiere;
use App\Entity\Notification;
use App\Entity\Submission;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Loads a realistic demo data set (teachers, classes, students, tasks,
 * exams, grades) so every screen of the app has something to show.
 *
 * Dates are relative to "now", so the data always looks current.
 */
#[AsCommand(
    name: 'app:seed-demo',
    description: 'Load demo accounts, classes, tasks, exams and grades',
)]
class SeedDemoDataCommand extends Command
{
    public const DEMO_PASSWORD = 'Demo1234!';
    private const MARKER_EMAIL = 'teacher@demo.local';

    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserRepository              $users,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('if-empty', null, InputOption::VALUE_NONE, 'Do nothing when demo data is already loaded');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->users->findOneBy(['email' => self::MARKER_EMAIL])) {
            if ($input->getOption('if-empty')) {
                $io->writeln('Demo data already loaded, skipping.');
                return Command::SUCCESS;
            }
            $io->error('Demo data is already loaded (teacher@demo.local exists). Reset the database to reload it.');
            return Command::FAILURE;
        }

        mt_srand(2026); // reproducible grades

        // ── Teachers ─────────────────────────────────────────
        $sami  = $this->user('teacher@demo.local', 'Sami', 'Ben Ali', 'ROLE_TEACHER', note: 'Department: Computer Science');
        $leila = $this->user('teacher2@demo.local', 'Leila', 'Trabelsi', 'ROLE_TEACHER', note: 'Department: Mathematics');

        // ── Classes ──────────────────────────────────────────
        $gl2 = $this->classe('Software Engineering 2', 'GL2', 'Semester 3', 'cs', 'Second-year software engineering students.', 'Mon & Wed 08:30–10:00', 30);
        $rt2 = $this->classe('Networks & Telecom 2', 'RT2', 'Semester 3', 'cs', 'Second-year networks and telecommunications students.', 'Tue & Thu 10:15–11:45', 30);

        // ── Subjects (a subject links a teacher to a class) ──
        $algo  = $this->matiere('Algorithms', 3.0, 'fa-code', '145', $sami, $gl2);
        $db    = $this->matiere('Databases', 2.0, 'fa-database', '214', $sami, $gl2);
        $maths = $this->matiere('Mathematics', 2.5, 'fa-square-root-alt', '38', $leila, $gl2);
        $net   = $this->matiere('Computer Networks', 3.0, 'fa-network-wired', '145', $sami, $rt2);
        $prob  = $this->matiere('Probability', 2.0, 'fa-square-root-alt', '38', $leila, $rt2);

        // ── Students ─────────────────────────────────────────
        $gl2Students = [
            $this->user('student@demo.local', 'Yasmine', 'Gharbi', 'ROLE_STUDENT', $gl2),
            $this->user('student2@demo.local', 'Omar', 'Jaziri', 'ROLE_STUDENT', $gl2),
            $this->user('student3@demo.local', 'Ines', 'Mabrouk', 'ROLE_STUDENT', $gl2),
            $this->user('student4@demo.local', 'Mehdi', 'Bouazizi', 'ROLE_STUDENT', $gl2),
        ];
        $rt2Students = [
            $this->user('student5@demo.local', 'Rania', 'Hammami', 'ROLE_STUDENT', $rt2),
            $this->user('student6@demo.local', 'Karim', 'Saidi', 'ROLE_STUDENT', $rt2),
        ];

        // Whitelisted but not registered yet: lets you try the sign-up flow.
        $this->invite('newstudent@demo.local', 'ROLE_STUDENT', classe: 'GL2', note: 'Demo invite — register with this address');
        $this->invite('newteacher@demo.local', 'ROLE_TEACHER', departement: 'Physics', note: 'Demo invite — register with this address');

        // ── Tasks, exams and grades ──────────────────────────
        $plan = [
            [$algo,  $gl2Students, ['Sorting algorithms report', 'Graph traversal lab', 'Dynamic programming exercises', 'Complexity analysis quiz']],
            [$db,    $gl2Students, ['ER diagram for a library', 'SQL joins worksheet', 'Normalisation project']],
            [$maths, $gl2Students, ['Linear algebra problem set', 'Series and sequences homework']],
            [$net,   $rt2Students, ['Subnetting exercises', 'Wireshark capture analysis', 'Routing protocols presentation']],
            [$prob,  $rt2Students, ['Random variables problem set', 'Bayes theorem case study']],
        ];

        // Deadline offsets in days and their status; past tasks get graded.
        $schedule = [
            [-12, Task::STATUT_CLOSED, Task::PRIORITE_MEDIUM],
            [-3,  Task::STATUT_REVIEW, Task::PRIORITE_HIGH],
            [2,   Task::STATUT_OPEN,   Task::PRIORITE_HIGH],
            [9,   Task::STATUT_OPEN,   Task::PRIORITE_LOW],
        ];

        $taskCount = $gradeCount = 0;
        foreach ($plan as $i => [$matiere, $students, $titles]) {
            foreach ($titles as $j => $title) {
                [$offset, $status, $priority] = $schedule[$j % count($schedule)];
                $task = $this->task($matiere, $title, $offset + $i, $status, $priority);
                $taskCount++;

                if ($status === Task::STATUT_OPEN) {
                    continue;
                }
                foreach ($students as $student) {
                    $submission = (new Submission())
                        ->setTask($task)
                        ->setStudent($student)
                        ->setDateDepot($this->days($offset + $i - 1))
                        ->setStatut(Submission::STATUT_SUBMITTED);
                    $this->em->persist($submission);

                    if ($status === Task::STATUT_CLOSED) {
                        $value = round(mt_rand(80, 195) / 10, 1);
                        $grade = (new Grade())
                            ->setSubmission($submission)
                            ->setStudent($student)
                            ->setMatiere($matiere)
                            ->setValeur($value)
                            ->setLettre($this->letter($value))
                            ->setCommentaire($value >= 14 ? 'Great work, well structured.' : ($value >= 10 ? 'Good effort, review the details.' : 'Please come to office hours.'))
                            ->setIsPublished(true);
                        $this->em->persist($grade);
                        $gradeCount++;
                    }
                }
            }

            // One past and one upcoming exam per subject
            $this->exam($matiere, -20 + $i, 'A' . (10 + $i), Examen::TYPE_DS, 90);
            $this->exam($matiere, 4 + $i * 5, 'B' . (20 + $i), Examen::TYPE_EXAMEN, 120);
        }

        // ── Notifications for the main demo student ─────────
        $yasmine = $gl2Students[0];
        foreach ([
            [Notification::TYPE_TASK,   'New task "Dynamic programming exercises" posted in Algorithms.'],
            [Notification::TYPE_GRADE,  'Your grade for "Sorting algorithms report" is available.'],
            [Notification::TYPE_EXAMEN, 'Algorithms exam scheduled in room B20.'],
        ] as [$type, $message]) {
            $this->em->persist((new Notification())->setUser($yasmine)->setType($type)->setMessage($message));
        }

        $this->em->flush();

        $io->success(sprintf('Demo data loaded: 2 teachers, 6 students, 2 classes, 5 subjects, %d tasks, 10 exams, %d grades.', $taskCount, $gradeCount));
        $io->table(['Role', 'E-mail', 'Password'], [
            ['Teacher', 'teacher@demo.local', self::DEMO_PASSWORD],
            ['Teacher', 'teacher2@demo.local', self::DEMO_PASSWORD],
            ['Student', 'student@demo.local (… student6@demo.local)', self::DEMO_PASSWORD],
            ['Invite',  'newstudent@demo.local / newteacher@demo.local', 'choose at /register'],
        ]);

        return Command::SUCCESS;
    }

    private function user(string $email, string $first, string $last, string $role, ?Classe $classe = null, ?string $note = null): User
    {
        $user = (new User())->setEmail($email)->setPrenom($first)->setNom($last)->setRoles([$role]);
        $user->setPassword($this->hasher->hashPassword($user, self::DEMO_PASSWORD));
        if ($classe) {
            $user->addClass($classe);
        }
        $this->em->persist($user);

        $this->em->persist(
            (new AllowedEmail())
                ->setEmail($email)
                ->setRole($role)
                ->setClasse($classe?->getCode())
                ->setIsRegistered(true)
                ->setNote($note ?? 'Demo account')
        );

        return $user;
    }

    private function invite(string $email, string $role, ?string $classe = null, ?string $departement = null, ?string $note = null): void
    {
        $this->em->persist(
            (new AllowedEmail())
                ->setEmail($email)
                ->setRole($role)
                ->setClasse($classe)
                ->setDepartement($departement)
                ->setNote($note)
        );
    }

    private function classe(string $name, string $code, string $semester, string $category, string $description, string $schedule, int $max): Classe
    {
        $classe = (new Classe())
            ->setNom($name)->setCode($code)->setNiveau($semester)->setCategory($category)
            ->setDescription($description)->setSchedule($schedule)->setMaxStudents($max);
        $this->em->persist($classe);

        return $classe;
    }

    private function matiere(string $name, float $coef, string $icon, string $color, User $teacher, Classe $classe): Matiere
    {
        $matiere = (new Matiere())
            ->setNom($name)->setCoefficient($coef)->setIcone($icon)->setCouleur($color)
            ->setTeacher($teacher)->setClasse($classe);
        $this->em->persist($matiere);

        return $matiere;
    }

    private function task(Matiere $matiere, string $title, int $offsetDays, string $status, string $priority): Task
    {
        $task = (new Task())
            ->setTitre($title)
            ->setDescription(sprintf('%s — submit your work on the platform before the deadline.', $title))
            ->setDateLimite($this->days($offsetDays)->setTime(23, 59))
            ->setStatut($status)
            ->setPriorite($priority)
            ->setPointsMax(20)
            ->setMatiere($matiere);
        $this->em->persist($task);

        return $task;
    }

    private function exam(Matiere $matiere, int $offsetDays, string $room, string $type, int $minutes): void
    {
        $start = $this->days($offsetDays)->setTime(9, 0);
        $this->em->persist(
            (new Examen())
                ->setMatiere($matiere)
                ->setDateExamen($start)
                ->setHeureFin((clone $start)->modify(sprintf('+%d minutes', $minutes)))
                ->setSalle('Room ' . $room)
                ->setType($type)
                ->setDureeMinutes($minutes)
        );
    }

    private function days(int $offset): \DateTime
    {
        return (new \DateTime('today'))->modify(sprintf('%+d days', $offset));
    }

    private function letter(float $value): string
    {
        return match (true) {
            $value >= 16 => 'A',
            $value >= 14 => 'B',
            $value >= 12 => 'C',
            $value >= 10 => 'D',
            default      => 'F',
        };
    }
}
