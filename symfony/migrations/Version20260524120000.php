<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add departement and classe columns to allowed_email';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE allowed_email ADD departement VARCHAR(100) DEFAULT NULL, ADD classe VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE allowed_email DROP departement, DROP classe');
    }
}
