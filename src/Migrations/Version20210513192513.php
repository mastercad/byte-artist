<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210513192513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update Foreign Keys for Project Tags';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof MySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        if ((int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = \'project_tags\'
               AND CONSTRAINT_NAME = \'FK_562D5C3E14A1EC2\'
               AND CONSTRAINT_TYPE = \'FOREIGN KEY\''
        ) > 0) {
            $this->addSql('ALTER TABLE `project_tags` DROP FOREIGN KEY `FK_562D5C3E14A1EC2`');
        }

        $this->addSql(
            'ALTER TABLE `project_tags` ADD CONSTRAINT `FK_562D5C3E14A1EC2`'
            .' FOREIGN KEY (`project_fk`) REFERENCES `projects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE'
        );

        if ((int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = \'project_tags\'
               AND CONSTRAINT_NAME = \'FK_562D5C3EADF5624F\'
               AND CONSTRAINT_TYPE = \'FOREIGN KEY\''
        ) > 0) {
            $this->addSql('ALTER TABLE `project_tags` DROP FOREIGN KEY `FK_562D5C3EADF5624F`');
        }

        $this->addSql(
            'ALTER TABLE `project_tags` ADD CONSTRAINT `FK_562D5C3EADF5624F`'
            .' FOREIGN KEY (`tag_fk`) REFERENCES `tags`(`id`) ON DELETE CASCADE ON UPDATE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof MySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        if ((int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = \'project_tags\'
               AND CONSTRAINT_NAME = \'FK_562D5C3E14A1EC2\'
               AND CONSTRAINT_TYPE = \'FOREIGN KEY\''
        ) > 0) {
            $this->addSql('ALTER TABLE `project_tags` DROP FOREIGN KEY `FK_562D5C3E14A1EC2`');
        }

        $this->addSql(
            'ALTER TABLE `project_tags` ADD CONSTRAINT `FK_562D5C3E14A1EC2`'
            .' FOREIGN KEY (`project_fk`) REFERENCES `projects`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT'
        );

        if ((int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = \'project_tags\'
               AND CONSTRAINT_NAME = \'FK_562D5C3EADF5624F\'
               AND CONSTRAINT_TYPE = \'FOREIGN KEY\''
        ) > 0) {
            $this->addSql('ALTER TABLE `project_tags` DROP FOREIGN KEY `FK_562D5C3EADF5624F`');
        }

        $this->addSql(
            'ALTER TABLE `project_tags` ADD CONSTRAINT `FK_562D5C3EADF5624F`'
            .' FOREIGN KEY (`tag_fk`) REFERENCES `tags`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT'
        );
    }
}
