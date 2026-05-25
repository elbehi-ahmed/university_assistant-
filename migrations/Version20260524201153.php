<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260524201153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE allowed_email (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, role VARCHAR(50) NOT NULL, departement VARCHAR(100) DEFAULT NULL, classe VARCHAR(100) DEFAULT NULL, is_registered TINYINT NOT NULL, note VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_243366ACE7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE classe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, niveau VARCHAR(50) NOT NULL, code VARCHAR(30) NOT NULL, UNIQUE INDEX UNIQ_8F87BF9677153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE etudiant (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE examen (id INT AUTO_INCREMENT NOT NULL, date_examen DATETIME NOT NULL, heure_fin DATETIME NOT NULL, salle VARCHAR(50) NOT NULL, type VARCHAR(20) NOT NULL, duree_minutes INT NOT NULL, matiere_id INT NOT NULL, INDEX IDX_514C8FECF46CD258 (matiere_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE grade (id INT AUTO_INCREMENT NOT NULL, valeur DOUBLE PRECISION NOT NULL, lettre VARCHAR(2) DEFAULT NULL, commentaire LONGTEXT DEFAULT NULL, is_published TINYINT NOT NULL, submission_id INT NOT NULL, student_id INT NOT NULL, matiere_id INT NOT NULL, UNIQUE INDEX UNIQ_595AAE34E1FD4933 (submission_id), INDEX IDX_595AAE34CB944F1A (student_id), INDEX IDX_595AAE34F46CD258 (matiere_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE matiere (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, coefficient DOUBLE PRECISION NOT NULL, icone VARCHAR(50) DEFAULT NULL, couleur VARCHAR(30) DEFAULT NULL, teacher_id INT NOT NULL, classe_id INT NOT NULL, INDEX IDX_9014574A41807E1D (teacher_id), INDEX IDX_9014574A8F5EA509 (classe_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE submission (id INT AUTO_INCREMENT NOT NULL, date_depot DATETIME NOT NULL, statut VARCHAR(20) NOT NULL, task_id INT NOT NULL, student_id INT NOT NULL, INDEX IDX_DB055AF38DB60186 (task_id), INDEX IDX_DB055AF3CB944F1A (student_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tache (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) NOT NULL, description LONGTEXT DEFAULT NULL, date_limite DATETIME NOT NULL, priorite VARCHAR(10) NOT NULL, statut VARCHAR(10) NOT NULL, points_max INT DEFAULT NULL, matiere_id INT NOT NULL, INDEX IDX_527EDB25F46CD258 (matiere_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, photo_profil VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, is_active TINYINT NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_classe (user_id INT NOT NULL, classe_id INT NOT NULL, INDEX IDX_EAD5A4ABA76ED395 (user_id), INDEX IDX_EAD5A4AB8F5EA509 (classe_id), PRIMARY KEY (user_id, classe_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE examen ADD CONSTRAINT FK_514C8FECF46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE grade ADD CONSTRAINT FK_595AAE34E1FD4933 FOREIGN KEY (submission_id) REFERENCES submission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE grade ADD CONSTRAINT FK_595AAE34CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE grade ADD CONSTRAINT FK_595AAE34F46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT FK_9014574A41807E1D FOREIGN KEY (teacher_id) REFERENCES `user` (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT FK_9014574A8F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE submission ADD CONSTRAINT FK_DB055AF38DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE submission ADD CONSTRAINT FK_DB055AF3CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25F46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_classe ADD CONSTRAINT FK_EAD5A4ABA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_classe ADD CONSTRAINT FK_EAD5A4AB8F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE examen DROP FOREIGN KEY FK_514C8FECF46CD258');
        $this->addSql('ALTER TABLE grade DROP FOREIGN KEY FK_595AAE34E1FD4933');
        $this->addSql('ALTER TABLE grade DROP FOREIGN KEY FK_595AAE34CB944F1A');
        $this->addSql('ALTER TABLE grade DROP FOREIGN KEY FK_595AAE34F46CD258');
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY FK_9014574A41807E1D');
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY FK_9014574A8F5EA509');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE submission DROP FOREIGN KEY FK_DB055AF38DB60186');
        $this->addSql('ALTER TABLE submission DROP FOREIGN KEY FK_DB055AF3CB944F1A');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25F46CD258');
        $this->addSql('ALTER TABLE user_classe DROP FOREIGN KEY FK_EAD5A4ABA76ED395');
        $this->addSql('ALTER TABLE user_classe DROP FOREIGN KEY FK_EAD5A4AB8F5EA509');
        $this->addSql('DROP TABLE allowed_email');
        $this->addSql('DROP TABLE classe');
        $this->addSql('DROP TABLE etudiant');
        $this->addSql('DROP TABLE examen');
        $this->addSql('DROP TABLE grade');
        $this->addSql('DROP TABLE matiere');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE submission');
        $this->addSql('DROP TABLE tache');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE user_classe');
    }
}
