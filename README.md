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
