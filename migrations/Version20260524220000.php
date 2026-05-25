<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add teacher class metadata columns for description, schedule, category and capacity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE classe ADD category VARCHAR(30) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD schedule VARCHAR(255) DEFAULT NULL, ADD max_students INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE classe DROP COLUMN category, DROP COLUMN description, DROP COLUMN schedule, DROP COLUMN max_students');
    }
}
