<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260409120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_public column to blog table (default 1 = visible)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `blog` ADD COLUMN `is_public` TINYINT(1) NOT NULL DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `blog` DROP COLUMN `is_public`');
    }
}
