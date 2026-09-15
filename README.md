# Assistant Universitaire Intelligent
Une plateforme web qui aide l’étudiant à organiser toute sa vie universitaire (cours, devoirs, examens) dans un seul espace, avec une touche d’intelligence pour gérer les priorités automatiquement.
# Objectif
Faciliter la gestion académique de l’étudiant en lui permettant de :
- Suivre ses matières
- ses devoirs et tâches
- Ne jamais rater ses examens
- Visualiser ses urgences et statistiques
- Recevoir des indications intelligentes sur les priorités
# Fonctionnalités
1.Gestion des utilisateurs:
- Inscription / Connexion
- Sécurité avec Symfony Security
- Système de rôles :

  - ROLE_ETUDIANT → Utilisateur principal
  - ROLE_ADMIN → (optionnel) Gestion globale

2.Gestion académique:
- Matières:

L’étudiant peut :
- Ajouter une matière
- Modifier une matière
- Supprimer une matière
- Associer un coefficient à chaque matière

### Tâches & Devoirs :
  
| Champ        | Description |

|-------------|-------------|

| Titre       | Nom du devoir |

| Description | Détails du travail |

| Date limite | Date de remise |

| Priorité    | Calculée automatiquement |

| Statut      | À faire / Terminé |

###  Examens

| Champ   | Description |

|--------|-------------|

| Matière | Matière concernée |

| Date    | Date de l’examen |

| Salle   | Salle de l’examen |

# Modèle de données simplifié
Entités principales :
- User :
  - id
  - email
  - password
  - roles
- Matiere
  - id
  - nom
  - coefficient
  - user
- Tache
  - id
  - titre
  - description
  - dateLimite
  - priorite
  - statut
  - matiere
  - user
- Examen
  - id
  - date
  - salle
  - matiere
  - user
  
---

A modern web application built with the Symfony framework. This guide provides clear instructions to help you set up, configure, and run the project locally.

## Prerequisites

Make sure you have the following installed on your local machine:
- **PHP** (version 8.2 or higher recommended)
- **Composer** (Dependency Manager for PHP)
- **Symfony CLI** (Optional, but recommended for running the local web server)
- **Database Server** (MySQL, PostgreSQL, or SQLite)

---

## Installation & Setup

Follow these steps to get your development environment running:

### 1. Clone the Repository
If you haven't already cloned the repository to your local machine, run:
```bash
git clone https://github.com/your-username/your-new-public-repo.git
cd your-new-public-repo
```

### 2. Install PHP Dependencies
Install the required project dependencies using Composer:
```bash
composer install
```

### 3. Configure Environment Variables
Symfony uses a `.env` file to configure environment-specific settings such as database connections. 
1. Copy or rename the configuration if needed, or make sure your `.env` file is properly set up.
2. Update the `DATABASE_URL` line in your `.env` file to match your local database credentials:

```dotenv
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0&charset=utf8mb4"
```

---

## Database Setup & Migrations

### 1. Create the Database
If your database does not exist yet, create it using the Symfony CLI or Doctrine:
```bash
php bin/console doctrine:database:create
```

### 2. Run Database Migrations
Apply any existing database migrations to set up your database schema:
```bash
php bin/console doctrine:migrations:migrate
```

---

## Running the Project

You can run the project locally using either the Symfony CLI or PHP's built-in web server.

### Option A: Using Symfony CLI (Recommended)
Start the local web server:
```bash
symfony server:start
```
*(To stop the server later, press `Ctrl + C` or run `symfony server:stop`)*

### Option B: Using PHP Built-in Server
If you don't use the Symfony CLI, you can start PHP's built-in server targeting the `public/` directory:
```bash
php -S localhost:8000 -t public/
```

Once running, open your browser and navigate to `http://localhost:8000` (or the URL provided in your terminal).

---

## Running Tests

To run the test suite using PHPUnit, execute:
```bash
php bin/phpunit
