<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Entity\AllowedEmail;
use App\Entity\User;
use Doctrine\DBAL\Configuration as DbalConfiguration;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

session_start();

$dotenv = new Dotenv();
$dotenv->bootEnv(__DIR__ . '/../.env');

function createEntityManager(): EntityManager
{
    static $entityManager = null;

    if ($entityManager instanceof EntityManager) {
        return $entityManager;
    }

    $databaseUrl = $_SERVER['DATABASE_URL'] ?? '';
    if ($databaseUrl === '') {
        throw new RuntimeException('DATABASE_URL is not configured.');
    }

    $parsedUrl = parse_url($databaseUrl);
    if ($parsedUrl === false) {
        throw new RuntimeException('DATABASE_URL is invalid.');
    }

    parse_str($parsedUrl['query'] ?? '', $queryParams);

    $dbalConfiguration = new DbalConfiguration();
    $connection = DriverManager::getConnection([
        'driver' => 'pdo_mysql',
        'host' => $parsedUrl['host'] ?? '127.0.0.1',
        'port' => (int) ($parsedUrl['port'] ?? 3306),
        'dbname' => ltrim($parsedUrl['path'] ?? '', '/'),
        'user' => $parsedUrl['user'] ?? '',
        'password' => $parsedUrl['pass'] ?? '',
        'charset' => $queryParams['charset'] ?? 'utf8mb4',
        'serverVersion' => $queryParams['serverVersion'] ?? '8.0.32',
    ], $dbalConfiguration);

    $entityManager = new EntityManager(
        $connection,
        ORMSetup::createAttributeMetadataConfiguration([
            __DIR__ . '/../src/Entity',
        ], ($_SERVER['APP_ENV'] ?? 'dev') === 'dev')
    );

    return $entityManager;
}

function saveWhitelistEntry(array $payload): array
{
    $email = strtolower(trim((string) ($payload['email'] ?? '')));
    $role = trim((string) ($payload['role'] ?? 'ROLE_STUDENT'));
    $departement = trim((string) ($payload['departement'] ?? ''));
    $classe = trim((string) ($payload['classe'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Invalid email address.'];
    }

    $normalizedRole = $role !== '' ? $role : 'ROLE_STUDENT';
    if ($normalizedRole === 'ROLE_TEACHER') {
        $departement = $departement !== '' ? $departement : null;
        $classe = null;
    } elseif ($normalizedRole === 'ROLE_STUDENT') {
        $departement = null;
        $classe = $classe !== '' ? $classe : null;
    } else {
        $departement = null;
        $classe = null;
    }

    $entityManager = createEntityManager();
    $connection = $entityManager->getConnection();

    try {
        $existing = $connection->fetchOne('SELECT 1 FROM allowed_email WHERE email = ?', [$email]);
        if ($existing !== false) {
            return ['ok' => false, 'error' => 'This email is already in the whitelist.'];
        }

        $connection->insert('allowed_email', [
            'email' => $email,
            'role' => $normalizedRole,
            'is_registered' => 0,
            'departement' => $departement,
            'classe' => $classe,
        ]);
    } catch (Throwable $exception) {
        return ['ok' => false, 'error' => 'Unable to save the email. Please try again.'];
    }

    return ['ok' => true, 'message' => 'Email ' . $email . ' added to whitelist.'];
}

function registerUser(array $payload): array
{
    $firstname = trim((string) ($payload['firstname'] ?? ''));
    $lastname = trim((string) ($payload['lastname'] ?? ''));
    $email = strtolower(trim((string) ($payload['email'] ?? '')));
    $password = (string) ($payload['password'] ?? '');
    $confirm = (string) ($payload['confirm'] ?? '');
    $acceptedTerms = isset($payload['terms']);

    if ($firstname === '' || $lastname === '' || $email === '' || $password === '') {
        return ['ok' => false, 'error' => 'All fields are required.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Invalid email address.'];
    }

    if (strlen($password) < 8) {
        return ['ok' => false, 'error' => 'Password must be at least 8 characters.'];
    }

    if ($password !== $confirm) {
        return ['ok' => false, 'error' => 'Passwords do not match.'];
    }

    if (!$acceptedTerms) {
        return ['ok' => false, 'error' => 'You must accept the Terms of Service.'];
    }

    $entityManager = createEntityManager();
    $connection = $entityManager->getConnection();

    $allowedEmail = $connection->fetchAssociative(
        'SELECT id, role, is_registered FROM allowed_email WHERE email = ?',
        [$email]
    );

    if ($allowedEmail === false) {
        return ['ok' => false, 'error' => 'This email address is not authorized to register. Please contact an administrator.'];
    }

    if ((int) $allowedEmail['is_registered'] === 1) {
        return ['ok' => false, 'error' => 'An account already exists for this email address.'];
    }

    $existingUser = $connection->fetchAssociative(
        'SELECT id FROM user WHERE email = ?',
        [$email]
    );

    if ($existingUser !== false) {
        return ['ok' => false, 'error' => 'An account already exists for this email address.'];
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    if ($hashedPassword === false) {
        return ['ok' => false, 'error' => 'Unable to hash the password. Please try again.'];
    }

    $user = new User();
    $user->setPrenom($firstname);
    $user->setNom($lastname);
    $user->setEmail($email);
    $user->setRoles([(string) $allowedEmail['role']]);
    $user->setPassword($hashedPassword);

    $connection->beginTransaction();

    try {
        $entityManager->persist($user);
        $entityManager->flush();
        $connection->update('allowed_email', ['is_registered' => 1], ['id' => (int) $allowedEmail['id']]);
        $connection->commit();
    } catch (Throwable $exception) {
        $connection->rollBack();

        return ['ok' => false, 'error' => 'Unable to create your account. Please try again.'];
    }

    return ['ok' => true];
}

function authenticateUser(string $email, string $password): array
{
    $entityManager = createEntityManager();
    $connection = $entityManager->getConnection();

    $user = $connection->fetchAssociative(
        'SELECT id, email, roles, password, prenom, nom FROM user WHERE email = ?',
        [strtolower(trim($email))]
    );

    if ($user === false || !is_string($user['password'])) {
        return ['ok' => false, 'error' => 'Invalid email or password.'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['ok' => false, 'error' => 'Invalid email or password.'];
    }

    $roles = json_decode((string) $user['roles'], true);
    if (!is_array($roles)) {
        $roles = [];
    }

    $normalizedRoles = array_map(static fn(string $role): string => strtoupper(trim($role)), $roles);

    if (in_array('ROLE_ADMIN', $normalizedRoles, true) || in_array('ADMIN', $normalizedRoles, true)) {
        $role = 'admin';
    } elseif (in_array('ROLE_TEACHER', $normalizedRoles, true) || in_array('TEACHER', $normalizedRoles, true)) {
        $role = 'teacher';
    } else {
        $role = 'student';
    }

    return [
        'ok' => true,
        'role' => $role,
        'user' => [
            'email' => (string) $user['email'],
            'prenom' => (string) ($user['prenom'] ?? ''),
            'nom' => (string) ($user['nom'] ?? ''),
            'role' => $role,
        ],
    ];
}

function getSessionUser(): ?array
{
    if (!isset($_SESSION['auth_user']) || !is_array($_SESSION['auth_user'])) {
        return null;
    }

    $user = $_SESSION['auth_user'];
    if (!is_string($user['email'] ?? null) || !is_string($user['prenom'] ?? null) || !is_string($user['nom'] ?? null) || !is_string($user['role'] ?? null)) {
        return null;
    }

    return $user;
}

function getConnectedProfile(?array $sessionUser): array
{
    if ($sessionUser === null) {
        return [];
    }

    $email = strtolower(trim((string) ($sessionUser['email'] ?? '')));
    if ($email === '') {
        return $sessionUser;
    }

    $entityManager = createEntityManager();
    $connection = $entityManager->getConnection();

    $user = $connection->fetchAssociative(
        'SELECT id, email, prenom, nom FROM user WHERE email = ?',
        [$email]
    );

    $allowed = $connection->fetchAssociative(
        'SELECT departement, classe FROM allowed_email WHERE email = ? AND is_registered = 1',
        [$email]
    );

    $prenom = trim((string) ($user['prenom'] ?? $sessionUser['prenom'] ?? ''));
    $nom = trim((string) ($user['nom'] ?? $sessionUser['nom'] ?? ''));
    $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));

    return [
        'id' => isset($user['id']) ? (int) $user['id'] : null,
        'email' => (string) ($user['email'] ?? $sessionUser['email'] ?? $email),
        'prenom' => $prenom,
        'nom' => $nom,
        'role' => strtolower(trim((string) ($sessionUser['role'] ?? 'student'))),
        'department' => $allowed['departement'] ?? null,
        'classe' => $allowed['classe'] ?? null,
        'initials' => $initials,
        'studentId' => isset($user['id']) ? (string) $user['id'] : $email,
    ];
}

function getCurrentRole(?array $sessionUser, ?string $queryRole, string $path): string
{
    if ($sessionUser !== null) {
        return strtolower(trim($sessionUser['role']));
    }

    $role = $queryRole;
    if ($role === null) {
        if (str_starts_with($path, 'teacher')) {
            $role = 'teacher';
        } elseif (str_starts_with($path, 'admin')) {
            $role = 'admin';
        } else {
            $role = 'student';
        }
    }

    return strtolower(trim($role));
}

function getPreviewUser(?array $sessionUser, string $role): object
{
    if ($sessionUser !== null) {
        return (object) [
            'prenom' => $sessionUser['prenom'],
            'nom' => $sessionUser['nom'],
        ];
    }

    return match ($role) {
        'admin' => (object) ['prenom' => 'Youssef', 'nom' => 'Admin'],
        'teacher' => (object) ['prenom' => 'Karim', 'nom' => 'Saidi'],
        default => (object) ['prenom' => 'Amina', 'nom' => 'Ben Salem'],
    };
}

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$path = trim($uri, '/');
if ($path === '') {
    $path = '/';
}

$sessionUser = getSessionUser();
$role = getCurrentRole($sessionUser, $_GET['role'] ?? null, $path);
$GLOBALS['preview_role'] = $role;

$error = null;
$flashMessage = null;
$flashType = null;

if ($path === 'register' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $result = registerUser($_POST);
    if ($result['ok']) {
        header('Location: /login');
        exit;
    }

    $error = $result['error'] ?? 'Unable to create your account.';
}

if ($path === 'login' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = (string) ($_POST['_username'] ?? '');
    $password = (string) ($_POST['_password'] ?? '');

    $result = authenticateUser($email, $password);
    if ($result['ok']) {
        $_SESSION['auth_user'] = $result['user'] ?? null;
        $role = (string) ($result['role'] ?? 'student');
        $redirectPath = match ($role) {
            'admin' => '/admin/dashboard',
            'teacher' => '/teacher/dashboard',
            default => '/student/dashboard',
        };

        header('Location: ' . $redirectPath);
        exit;
    }

    $error = $result['error'] ?? 'Invalid email or password.';
}

if ($path === 'admin/whitelist' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $result = saveWhitelistEntry($_POST);
    $flashMessage = $result['message'] ?? ($result['error'] ?? null);
    $flashType = $result['ok'] ? 'success' : 'error';

    if ($result['ok']) {
        $_SESSION['admin_whitelist_flash'] = [
            'message' => $flashMessage,
            'type' => $flashType,
        ];
        header('Location: /admin/whitelist');
        exit;
    }
}

$allowedEmails = [];
if ($path === 'admin/whitelist') {
    $connection = createEntityManager()->getConnection();
    $allowedEmails = $connection->fetchAllAssociative(
        'SELECT id, email, role, is_registered AS isUsed, departement, classe, NULL AS note, NULL AS createdAt FROM allowed_email ORDER BY id DESC'
    );
}

$flashMessage = $flashMessage ?? ($_SESSION['admin_whitelist_flash']['message'] ?? null);
$flashType = $flashType ?? ($_SESSION['admin_whitelist_flash']['type'] ?? null);
if (isset($_SESSION['admin_whitelist_flash'])) {
    unset($_SESSION['admin_whitelist_flash']);
}

$routes = [
    '/' => 'front/index.html.twig',
    'login' => 'auth/login.html.twig',
    'register' => 'auth/register.html.twig',
    'student/dashboard' => 'student/dashboard.html.twig',
    'student/subjects' => 'student/matieres.html.twig',
    'student/exams' => 'student/exams.html.twig',
    'student/grades' => 'student/grades.html.twig',
    'student/settings' => 'student/student_settings.html.twig',
    'student/tasks' => 'student/tasks.html.twig',
    'teacher/dashboard' => 'teacher/dashboard.html.twig',
    'teacher/classes' => 'teacher/classes.html.twig',
    'teacher/tasks' => 'teacher/tasks.html.twig',
    'teacher/grades' => 'teacher/grades.html.twig',
    'teacher/students' => 'teacher/students.html.twig',
    'teacher/settings' => 'teacher/settings.html.twig',
    'admin/dashboard' => 'admin/dashboard.html.twig',
    'admin/users' => 'admin/users.html.twig',
    'admin/whitelist' => 'admin/whitelist.html.twig',
];

$defaultTemplate = $routes[$path] ?? null;
if ($defaultTemplate === null) {
    http_response_code(404);
    echo '<h1>Template not found</h1><p>Available routes: ' . implode(', ', array_keys($routes)) . '</p>';
    exit;
}

$user = getPreviewUser($sessionUser, $role);
$profile = getConnectedProfile($sessionUser);

$profilePrenom = (string) ($profile['prenom'] ?? '');
$profileNom = (string) ($profile['nom'] ?? '');
$profileEmail = (string) ($profile['email'] ?? '');
$profileInitials = (string) ($profile['initials'] ?? '');

$student = (object) [
    'prenom' => $profilePrenom !== '' ? $profilePrenom : ($sessionUser['prenom'] ?? 'Amina'),
    'nom' => $profileNom !== '' ? $profileNom : ($sessionUser['nom'] ?? 'Ben Salem'),
    'email' => $profileEmail !== '' ? $profileEmail : ($sessionUser['email'] ?? 'amina.bensalem@insat.ucar.tn'),
    'studentId' => $profile['studentId'] ?? 'N/A',
    'classe' => $profile['classe'] ?? null,
];

$teacher = (object) [
    'prenom' => $profilePrenom !== '' ? $profilePrenom : ($sessionUser['prenom'] ?? 'Karim'),
    'nom' => $profileNom !== '' ? $profileNom : ($sessionUser['nom'] ?? 'Saidi'),
    'email' => $profileEmail !== '' ? $profileEmail : ($sessionUser['email'] ?? 'karim.saidi@insat.ucar.tn'),
    'department' => $profile['department'] ?? 'Computer Science',
    'initials' => $profileInitials !== '' ? $profileInitials : 'KS',
];

$notifications = (object) [
    'examReminder' => true,
    'taskAlert' => true,
    'weeklySummary' => false,
    'submissionAlerts' => true,
    'examReminders' => true,
];

$preferences = (object) [
    'defaultGradingScale' => 'Percentage (0-100)',
    'autoCalculateAverage' => true,
];

$departments = ['Computer Science', 'Mathematics', 'Physics', 'Electronics'];
$gradingScales = ['Percentage (0-100)', 'Letter Grade (A-F)', 'Points (0-20)'];

$request = new class($path, $role) {
    public function __construct(
        public string $path,
        public string $role,
    ) {}

    public function attributes(): array
    {
        return ['_route' => match ($this->path) {
            'teacher/dashboard' => 'app_teacher_dashboard',
            'teacher/classes' => 'app_teacher_classes',
            'teacher/tasks' => 'app_teacher_tasks',
            'teacher/grades' => 'app_teacher_grades',
            'teacher/students' => 'app_teacher_students',
            'teacher/settings' => 'app_teacher_settings',
            'admin/dashboard' => 'app_admin_dashboard',
            'admin/users' => 'app_admin_users',
            'admin/whitelist' => 'app_admin_whitelist',
            'student/dashboard' => 'app_student_dashboard',
            'student/subjects' => 'app_student_subjects',
            'student/exams' => 'app_student_exams',
            'student/grades' => 'app_student_grades',
            'student/settings' => 'app_student_settings',
            'student/tasks' => 'app_student_tasks',
            'login' => 'app_login',
            'register' => 'app_register',
            default => 'app_home',
        }];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes()[$key] ?? $default;
    }
};

$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, ['cache' => false]);

$twig->addFunction(new TwigFunction('asset', static fn(string $path): string => '/' . ltrim($path, '/')));
$twig->addFunction(new TwigFunction('path', static function (string $route): string {
    return match ($route) {
        'app_home' => '/',
        'app_login' => '/login',
        'app_register' => '/register',
        'app_student_dashboard' => '/student/dashboard',
        'app_student_subjects' => '/student/subjects',
        'app_student_exams' => '/student/exams',
        'app_student_grades' => '/student/grades',
        'app_student_settings' => '/student/settings',
        'app_student_tasks' => '/student/tasks',
        'app_teacher_dashboard' => '/teacher/dashboard',
        'app_teacher_classes' => '/teacher/classes',
        'app_teacher_tasks' => '/teacher/tasks',
        'app_teacher_grades' => '/teacher/grades',
        'app_teacher_students' => '/teacher/students',
        'app_teacher_settings' => '/teacher/settings',
        'app_admin_dashboard' => '/admin/dashboard',
        'app_admin_users' => '/admin/users',
        'app_admin_whitelist' => '/admin/whitelist',
        default => '#',
    };
}));
$twig->addFunction(new TwigFunction('is_granted', static fn(string $role): bool => match ($role) {
    'ROLE_ADMIN' => $GLOBALS['preview_role'] === 'admin',
    'ROLE_TEACHER' => $GLOBALS['preview_role'] === 'teacher',
    'ROLE_STUDENT' => $GLOBALS['preview_role'] === 'student',
    default => false,
}));
$twig->addFunction(new TwigFunction('csrf_token', static fn(string $id = 'authenticate'): string => 'preview-' . hash('sha256', $id)));
$twig->addFunction(new TwigFunction('last_username', static fn(): string => ''));
$twig->addFilter(new TwigFilter('trans', static fn(string $message, array $parameters = [], ?string $domain = null): string => $message));

$context = [
    'app' => (object) [
        'request' => $request,
        'user' => $user,
    ],
    'controller_name' => 'FrontendPreview',
    'student' => $student,
    'teacher' => $teacher,
    'notifications' => $notifications,
    'preferences' => $preferences,
    'departments' => $departments,
    'gradingScales' => $gradingScales,
    'urgentTaskCount' => 0,
    'upcomingExamCount' => 0,
    'activeClassCount' => 0,
    'openAssignmentCount' => 0,
    'allowed_emails' => $allowedEmails,
    'flash_message' => $flashMessage,
    'flash_type' => $flashType,
    'error' => $error,
];

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

echo $twig->render($defaultTemplate, $context);
