-- ============================================================
--  BASE DE DONNÉES : Plateforme de Gestion Universitaire
--  Tous les noms de colonnes et tables sont en français
--  Moteur : MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestion_universitaire
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gestion_universitaire;

-- ============================================================
-- TABLE : utilisateur
-- ============================================================
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


-- ============================================================
-- TABLE : matiere
-- ============================================================
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


-- ============================================================
-- TABLE : tache
-- ============================================================
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


-- ============================================================
-- TABLE : examen
-- ============================================================
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


-- ============================================================
-- INDEX SUPPLÉMENTAIRES (performances)
-- ============================================================

-- Recherche rapide des tâches par statut et date limite
CREATE INDEX idx_tache_statut_date
    ON tache (statut, date_limite);

-- Recherche rapide des examens à venir
CREATE INDEX idx_examen_date
    ON examen (date_examen);

-- Filtrage des tâches par priorité
CREATE INDEX idx_tache_priorite
    ON tache (priorite);


-- ============================================================
-- VUE : taches_urgentes
-- Affiche les tâches non terminées, triées par urgence
-- ============================================================
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


-- VUE : examens_a_venir
-- Affiche les examens futurs triés chronologiquement
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
