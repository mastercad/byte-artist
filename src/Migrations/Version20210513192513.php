<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210513192513 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Update Foreign Keys for Project Tags';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `project_tags` DROP FOREIGN KEY `FK_562D5C3E14A1EC2`; ALTER TABLE `project_tags` ADD CONSTRAINT `FK_562D5C3E14A1EC2` FOREIGN KEY (`project_fk`) REFERENCES `projects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `project_tags` DROP FOREIGN KEY `FK_562D5C3EADF5624F`; ALTER TABLE `project_tags` ADD CONSTRAINT `FK_562D5C3EADF5624F` FOREIGN KEY (`tag_fk`) REFERENCES `tags`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `project_tags` DROP FOREIGN KEY `FK_562D5C3E14A1EC2`; ALTER TABLE `project_tags` ADD CONSTRAINT `FK_562D5C3E14A1EC2` FOREIGN KEY (`project_fk`) REFERENCES `projects`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; ALTER TABLE `project_tags` DROP FOREIGN KEY `FK_562D5C3EADF5624F`; ALTER TABLE `project_tags` ADD CONSTRAINT `FK_562D5C3EADF5624F` FOREIGN KEY (`tag_fk`) REFERENCES `tags`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;');
    }
}
