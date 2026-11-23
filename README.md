# University Assistant

A web platform that brings a student's whole academic life — subjects, assignments, exams and grades — into one place, and gives teachers and administrators the tools to manage it.

Built with **Symfony 7.4**, **PHP 8.3**, **Doctrine ORM** and **MySQL 8**, and shipped with a ready-to-run **Docker** setup.

> 📘 **New here? Read the [User Guide](docs/USER_GUIDE.md)** for a walkthrough of every screen, role by role.

---

## Features

| Role | What they can do |
|------|------------------|
| **Student** | Dashboard with urgent tasks and upcoming exams · subjects of their class · assignments sorted by deadline and priority · exam calendar with countdowns · published grades with a coefficient-weighted average · profile and password settings |
| **Teacher** | Create and delete classes · post assignments with a deadline, priority and max points · follow submission rates · view grades per class and assignment · see the students of each class |
| **Administrator** | Platform statistics · control who may register through an e-mail **whitelist** (with role and class) · search and filter all users · revoke access |

**Security:** registration is invite-only (whitelist), passwords are hashed, forms are CSRF-protected, each area (`/student`, `/teacher`, `/admin`) is restricted to its role, and suspended accounts cannot sign in.

---

## Quick start (Docker)

Requires only [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or Docker Engine + Compose v2).

```bash
git clone https://github.com/elbehi-ahmed/university_assistant-.git
cd university_assistant-
docker compose up -d --build
```

The first build takes a few minutes. Then open **http://localhost:8080** and sign in:

| Role | E-mail | Password |
|------|--------|----------|
| Administrator | `admin@example.com` | `ChangeMe123!` |
| Teacher | `teacher@demo.local` | `Demo1234!` |
| Student | `student@demo.local` | `Demo1234!` |

On start-up, the container waits for MySQL, runs the database migrations, creates the admin account and, in development mode, loads demo data (2 teachers, 6 students, 2 classes, tasks, exams and grades).

### Everyday commands

```bash
docker compose logs -f app                       # follow application logs
docker compose exec app php bin/console          # list Symfony console commands
docker compose exec app php bin/phpunit          # run the test suite
docker compose down                              # stop (data is kept)
docker compose down -v                           # stop and DELETE the database
```

### Development vs production mode

| | `docker compose up` | `docker compose -f compose.yaml up` |
|---|---|---|
| Mode | Development | Production |
| Code changes | Live (source folders are mounted) | Need a rebuild |
| Debug toolbar | Yes | No |
| Demo data | Loaded | Not loaded |
| MySQL port on host | `3307` | Not exposed |

**Before running in production**, set strong values for at least `APP_SECRET`, `ADMIN_PASSWORD`, `MYSQL_PASSWORD` and `MYSQL_ROOT_PASSWORD` as environment variables:

```bash
export APP_SECRET=$(openssl rand -hex 16)
export ADMIN_EMAIL=admin@your-university.tn ADMIN_PASSWORD='a-long-unique-password'
export MYSQL_PASSWORD='…' MYSQL_ROOT_PASSWORD='…'
docker compose -f compose.yaml up -d --build
```

### Configuration

Docker Compose reads these variables from your shell or from the `###> docker ###` block in `.env`:

| Variable | Default | Purpose |
|----------|---------|---------|
| `HTTP_PORT` | `8080` | Port the app is published on |
| `MYSQL_HOST_PORT` | `3307` | MySQL port on your machine (dev only) |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | `admin@example.com` / `ChangeMe123!` | First admin account, created if missing |
| `SEED_DEMO_DATA` | `1` in dev, `0` in prod | Load demo data on first start |
| `APP_SECRET` | random per start in prod | Symfony secret (sessions, CSRF) |
| `MYSQL_DATABASE` / `MYSQL_USER` / `MYSQL_PASSWORD` / `MYSQL_ROOT_PASSWORD` | `assist` / `app` / `app_password` / `root_password` | Database settings |

---

## Running without Docker

Requirements: PHP ≥ 8.2 with `pdo_mysql` and `intl`, Composer, MySQL 8 (or MariaDB).

```bash
composer install

# Point the app at your database in .env.local (not committed):
echo 'DATABASE_URL="mysql://USER:PASSWORD@127.0.0.1:3306/assist?serverVersion=8.0&charset=utf8mb4"' > .env.local

php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
php bin/console app:create-admin admin@example.com   # asks for a password
php bin/console app:seed-demo                        # optional demo data

symfony server:start      # or: php -S localhost:8000 -t public/
```

---

## Console commands

| Command | Description |
|---------|-------------|
| `app:create-admin <email> [--password=…]` | Create an administrator. Needed once, because only admins can edit the whitelist. |
| `app:seed-demo [--if-empty]` | Load demo accounts and data (password `Demo1234!`). |
| `doctrine:migrations:migrate` | Apply database migrations. |

---

## Project structure

```
├── src/
│   ├── Command/        Console commands (create admin, demo data)
│   ├── Controller/     Auth/, Student/, Teacher/, Admin/ — one controller per page
│   ├── Entity/         Doctrine entities (User, Classe, Matiere, Task, Examen, Grade, …)
│   ├── Repository/     Doctrine repositories
│   └── Security/       UserChecker (blocks suspended accounts)
├── templates/          Twig views, one folder per role
├── public/css, js/     Stylesheets and scripts per area
├── migrations/         Database schema history
├── config/             Symfony configuration (security.yaml holds roles & access rules)
├── docker/             Apache vhost, PHP settings, container entrypoint
├── tests/              PHPUnit tests
├── Dockerfile          Multi-stage image (dev / prod)
└── compose.yaml        App + MySQL stack (compose.override.yaml = dev settings)
```

### Data model

```
User ──< user_classe >── Classe ──< Matiere (subject: teacher + class + coefficient)
                                      ├──< Task ──< Submission ──1 Grade
                                      └──< Examen
User ──< Notification         AllowedEmail (registration whitelist: e-mail, role, class)
```

Roles: `ROLE_STUDENT`, `ROLE_TEACHER`, `ROLE_ADMIN` (an admin also has teacher and student rights).

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| `port is already allocated` | Another program uses 8080 or 3307. Set `HTTP_PORT=8090` or `MYSQL_HOST_PORT=3308` and run `docker compose up -d` again. |
| The app container keeps restarting | `docker compose logs app` — usually the database is still initialising on first start; it retries for two minutes. |
| "This email address is not authorized to register" | An admin must add the e-mail to the whitelist first (Admin → Whitelist). |
| "No class matches …" when a student registers | The class code in the whitelist entry must match an existing class (created by a teacher). |
| Start over with a clean database | `docker compose down -v && docker compose up -d` |
| Build stops with `additional privileges requested: … fs.read=Dockerfile` | A Compose "bake" quirk with lowercase drive letters on Windows (`c:\…`). Open the terminal at `C:\…`, or run `COMPOSE_BAKE=false docker compose up -d --build` (PowerShell: `$env:COMPOSE_BAKE='false'`). |
