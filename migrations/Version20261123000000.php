<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261123000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop the unused placeholder tables etudiant and tache (users and tasks live in `user` and `task`).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS etudiant');
        $this->addSql('DROP TABLE IF EXISTS tache');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE etudiant (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tache (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }
}
