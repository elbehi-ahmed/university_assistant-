
CREATE DATABASE IF NOT EXISTS gestion_universitaire
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gestion_universitaire;

CREATE TABLE utilisateur (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    email         VARCHAR(180)    NOT NULL,
    mot_de_passe  VARCHAR(255)    NOT NULL COMMENT 'Hash bcrypt',
    roles         JSON            NOT NULL COMMENT 'Ex : ["ROLE_ETUDIANT"]',
    prenom        VARCHAR(100)    NOT NULL,
    nom           VARCHAR(100)    NOT NULL,
    cree_le       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mis_a_jour_le DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                           ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT pk_utilisateur     PRIMARY KEY (id),
    CONSTRAINT uq_utilisateur_email UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Comptes des étudiants et administrateurs';

CREATE TABLE matiere (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nom             VARCHAR(150)    NOT NULL,
    coefficient     DECIMAL(4,2)    NOT NULL DEFAULT 1.00
                                    COMMENT 'Coefficient de pondération (ex : 2.00)',
    couleur         VARCHAR(7)      NULL     DEFAULT '#3B82F6'
                                    COMMENT 'Couleur HEX pour l\'affichage UI',
    utilisateur_id  INT UNSIGNED    NOT NULL,
    cree_le         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mis_a_jour_le   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT pk_matiere           PRIMARY KEY (id),
    CONSTRAINT fk_matiere_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Matières associées à un étudiant';

CREATE TABLE tache (
    id              INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    titre           VARCHAR(200)        NOT NULL,
    description     TEXT                NULL,
    date_limite     DATETIME            NOT NULL COMMENT 'Date et heure de remise',
    priorite        TINYINT UNSIGNED    NOT NULL DEFAULT 2
                                        COMMENT '1=Basse  2=Moyenne  3=Haute  4=Critique',
    statut          ENUM(
                        'a_faire',
                        'en_cours',
                        'termine',
                        'annule'
                    )                   NOT NULL DEFAULT 'a_faire',
    matiere_id      INT UNSIGNED        NULL     COMMENT 'Matière liée (optionnelle)',
    utilisateur_id  INT UNSIGNED        NOT NULL,
    cree_le         DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mis_a_jour_le   DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                 ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT pk_tache             PRIMARY KEY (id),
    CONSTRAINT fk_tache_matiere
        FOREIGN KEY (matiere_id)     REFERENCES matiere(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_tache_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT ck_tache_priorite CHECK (priorite BETWEEN 1 AND 4)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Devoirs et tâches académiques de l\'étudiant';

CREATE TABLE examen (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    date_examen     DATETIME        NOT NULL COMMENT 'Date et heure de début',
    salle           VARCHAR(100)    NOT NULL,
    duree_minutes   SMALLINT        NULL     DEFAULT 120
                                    COMMENT 'Durée de l\'examen en minutes',
    notes           TEXT            NULL     COMMENT 'Informations supplémentaires',
    matiere_id      INT UNSIGNED    NOT NULL,
    utilisateur_id  INT UNSIGNED    NOT NULL,
    cree_le         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mis_a_jour_le   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT pk_examen            PRIMARY KEY (id),
    CONSTRAINT fk_examen_matiere
        FOREIGN KEY (matiere_id)     REFERENCES matiere(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_examen_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Examens planifiés par matière';

CREATE INDEX idx_tache_statut_date
    ON tache (statut, date_limite);


CREATE INDEX idx_examen_date
    ON examen (date_examen);

CREATE INDEX idx_tache_priorite
    ON tache (priorite);


CREATE OR REPLACE VIEW taches_urgentes AS
SELECT
    t.id,
    t.titre,
    t.date_limite,
    t.priorite,
    t.statut,
    m.nom                                        AS matiere,
    m.couleur                                    AS couleur_matiere,
    DATEDIFF(t.date_limite, NOW())               AS jours_restants,
    CASE
        WHEN DATEDIFF(t.date_limite, NOW()) < 0  THEN 'en_retard'
        WHEN DATEDIFF(t.date_limite, NOW()) <= 2 THEN 'critique'
        WHEN DATEDIFF(t.date_limite, NOW()) <= 7 THEN 'urgent'
        ELSE                                          'normal'
    END                                          AS niveau_urgence,
    t.utilisateur_id
FROM  tache   t
JOIN  matiere m ON m.id = t.matiere_id
WHERE t.statut NOT IN ('termine', 'annule')
ORDER BY
    FIELD(t.statut, 'en_cours', 'a_faire'),
    t.priorite  DESC,
    t.date_limite ASC;



CREATE OR REPLACE VIEW examens_a_venir AS
SELECT
    e.id,
    e.date_examen,
    e.salle,
    e.duree_minutes,
    e.notes,
    m.nom       AS matiere,
    m.couleur   AS couleur_matiere,
    m.coefficient,
    DATEDIFF(e.date_examen, NOW())  AS jours_avant_examen,
    e.utilisateur_id
FROM  examen  e
JOIN  matiere m ON m.id = e.matiere_id
WHERE e.date_examen >= NOW()
ORDER BY e.date_examen ASC;


-- Données de test pour vérification
INSERT INTO utilisateur (email, mot_de_passe, roles, prenom, nom)
VALUES
    ('admin@univ.local', '$2y$10$abcdefghijklmnopqrstuvabcdefghi', '["ROLE_ADMIN"]', 'Claire', 'Durand'),
    ('etudiant1@univ.local', '$2y$10$1234567890123456789012', '["ROLE_ETUDIANT"]', 'Amine', 'Boukadida'),
    ('etudiant2@univ.local', '$2y$10$abcdefghijklmnopqrstuvwx', '["ROLE_ETUDIANT"]', 'Sara', 'Tiss'),
    ('etudiant3@univ.local', '$2y$10$zyxwvutsrqponmlkjihgfe', '["ROLE_ETUDIANT"]', 'Youssef', 'Namir');

INSERT INTO matiere (nom, coefficient, couleur, utilisateur_id)
VALUES
    ('Mathématiques', 2.50, '#EF4444', 2),
    ('Programmation Java', 3.00, '#2563EB', 2),
    ('Physique', 2.00, '#10B981', 3),
    ('Histoire', 1.50, '#F59E0B', 4),
    ('Anglais', 1.00, '#8B5CF6', 2);

INSERT INTO tache (titre, description, date_limite, priorite, statut, matiere_id, utilisateur_id)
VALUES
    ('Rendre le devoir de maths', 'Exercices sur dérivées et intégrales', DATE_ADD(NOW(), INTERVAL 3 DAY), 3, 'a_faire', 1, 2),
    ('Projet Java: gestion de bibliothèque', 'Concevoir classes, DAO et interface', DATE_ADD(NOW(), INTERVAL 7 DAY), 4, 'en_cours', 2, 2),
    ('Étude chapitre 3 physique', 'Préparer la fiche de révision sur l\'optique', DATE_ADD(NOW(), INTERVAL 5 DAY), 2, 'en_cours', 3, 3),
    ('Lire les pages 45-60 histoire', 'Réviser la période de la Révolution française', DATE_ADD(NOW(), INTERVAL 10 DAY), 1, 'a_faire', 4, 4),
    ('Préparer le test d\'anglais', 'Mémoriser les verbes irréguliers', DATE_ADD(NOW(), INTERVAL 2 DAY), 4, 'a_faire', 5, 2),
    ('Corriger le TP Java', 'Tester toutes les fonctionnalités et corriger les bugs', DATE_ADD(NOW(), INTERVAL -1 DAY), 3, 'en_cours', 2, 2);

INSERT INTO examen (date_examen, salle, duree_minutes, notes, matiere_id, utilisateur_id)
VALUES
    ('2026-05-10 09:00:00', 'Salle 101', 120, 'Série de questions niveau BTS', 1, 2),
    ('2026-05-14 14:00:00', 'Salle 202', 90, 'Épreuve pratique Java', 2, 2),
    ('2026-05-12 11:00:00', 'Salle 107', 120, 'Révision sur mécanique', 3, 3),
    ('2026-05-09 08:30:00', 'Salle 306', 60, 'Vocabulaire et compréhension écrite', 5, 2);

