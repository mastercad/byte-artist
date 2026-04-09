<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210513192857 extends AbstractMigration
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
               AND TABLE_NAME = \'blog_tags\'
               AND CONSTRAINT_NAME = \'FK_8F6C18B6ADF5624F\'
               AND CONSTRAINT_TYPE = \'FOREIGN KEY\''
        ) > 0) {
            $this->addSql('ALTER TABLE `blog_tags` DROP FOREIGN KEY `FK_8F6C18B6ADF5624F`');
        }

        $this->addSql(
            'ALTER TABLE `blog_tags` ADD CONSTRAINT `FK_8F6C18B6ADF5624F`'
            .' FOREIGN KEY (`tag_fk`) REFERENCES `tags`(`id`) ON DELETE CASCADE ON UPDATE CASCADE'
        );

        if ((int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = \'blog_tags\'
               AND CONSTRAINT_NAME = \'FK_8F6C18B6CDC77FC9\'
               AND CONSTRAINT_TYPE = \'FOREIGN KEY\''
        ) > 0) {
            $this->addSql('ALTER TABLE `blog_tags` DROP FOREIGN KEY `FK_8F6C18B6CDC77FC9`');
        }

        $this->addSql(
            'ALTER TABLE `blog_tags` ADD CONSTRAINT `FK_8F6C18B6CDC77FC9`'
            .' FOREIGN KEY (`blog_fk`) REFERENCES `blog`(`id`) ON DELETE CASCADE ON UPDATE CASCADE'
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
               AND TABLE_NAME = \'blog_tags\'
               AND CONSTRAINT_NAME = \'FK_8F6C18B6ADF5624F\'
               AND CONSTRAINT_TYPE = \'FOREIGN KEY\''
        ) > 0) {
            $this->addSql('ALTER TABLE `blog_tags` DROP FOREIGN KEY `FK_8F6C18B6ADF5624F`');
        }

        $this->addSql(
            'ALTER TABLE `blog_tags` ADD CONSTRAINT `FK_8F6C18B6ADF5624F`'
            .' FOREIGN KEY (`tag_fk`) REFERENCES `tags`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT'
        );

        if ((int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = \'blog_tags\'
               AND CONSTRAINT_NAME = \'FK_8F6C18B6CDC77FC9\'
               AND CONSTRAINT_TYPE = \'FOREIGN KEY\''
        ) > 0) {
            $this->addSql('ALTER TABLE `blog_tags` DROP FOREIGN KEY `FK_8F6C18B6CDC77FC9`');
        }

        $this->addSql(
            'ALTER TABLE `blog_tags` ADD CONSTRAINT `FK_8F6C18B6CDC77FC9`'
            .' FOREIGN KEY (`blog_fk`) REFERENCES `blog`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT'
        );
    }
}
