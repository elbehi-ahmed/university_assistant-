<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260429110452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE examen DROP FOREIGN KEY `fk_examen_etudiant`');
        $this->addSql('ALTER TABLE examen DROP FOREIGN KEY `fk_examen_matiere`');
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY `fk_matiere_etudiant`');
        $this->addSql('ALTER TABLE tache DROP FOREIGN KEY `fk_tache_etudiant`');
        $this->addSql('ALTER TABLE tache DROP FOREIGN KEY `fk_tache_matiere`');
        $this->addSql('DROP TABLE etudiant');
        $this->addSql('DROP TABLE examen');
        $this->addSql('DROP TABLE matiere');
        $this->addSql('DROP TABLE tache');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE etudiant (id INT UNSIGNED AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, prenom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, mot_de_passe VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, role ENUM(\'etudiant\', \'admin\') CHARACTER SET utf8mb4 DEFAULT \'\'\'etudiant\'\'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, cree_le DATETIME DEFAULT \'current_timestamp()\' NOT NULL, UNIQUE INDEX email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE examen (id INT UNSIGNED AUTO_INCREMENT NOT NULL, date_examen DATETIME NOT NULL, salle VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, matiere_id INT UNSIGNED NOT NULL, etudiant_id INT UNSIGNED NOT NULL, cree_le DATETIME DEFAULT \'current_timestamp()\' NOT NULL, INDEX fk_examen_matiere (matiere_id), INDEX idx_examen_date (etudiant_id, date_examen), INDEX IDX_514C8FECDDEAB1A3 (etudiant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE matiere (id INT UNSIGNED AUTO_INCREMENT NOT NULL, nom VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, coefficient NUMERIC(4, 2) DEFAULT \'1.00\' NOT NULL, etudiant_id INT UNSIGNED NOT NULL, cree_le DATETIME DEFAULT \'current_timestamp()\' NOT NULL, INDEX fk_matiere_etudiant (etudiant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE tache (id INT UNSIGNED AUTO_INCREMENT NOT NULL, titre VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, date_limite DATE NOT NULL, priorite ENUM(\'basse\', \'normale\', \'haute\', \'urgente\') CHARACTER SET utf8mb4 DEFAULT \'\'\'normale\'\'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, statut ENUM(\'a_faire\', \'en_cours\', \'termine\') CHARACTER SET utf8mb4 DEFAULT \'\'\'a_faire\'\'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, matiere_id INT UNSIGNED DEFAULT NULL, etudiant_id INT UNSIGNED NOT NULL, cree_le DATETIME DEFAULT \'current_timestamp()\' NOT NULL, INDEX fk_tache_matiere (matiere_id), INDEX idx_tache_limite (date_limite), INDEX idx_tache_statut (etudiant_id, statut), INDEX IDX_93872075DDEAB1A3 (etudiant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE examen ADD CONSTRAINT `fk_examen_etudiant` FOREIGN KEY (etudiant_id) REFERENCES etudiant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE examen ADD CONSTRAINT `fk_examen_matiere` FOREIGN KEY (matiere_id) REFERENCES matiere (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT `fk_matiere_etudiant` FOREIGN KEY (etudiant_id) REFERENCES etudiant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tache ADD CONSTRAINT `fk_tache_etudiant` FOREIGN KEY (etudiant_id) REFERENCES etudiant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tache ADD CONSTRAINT `fk_tache_matiere` FOREIGN KEY (matiere_id) REFERENCES matiere (id) ON DELETE SET NULL');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
