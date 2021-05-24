<?php

declare(strict_types=1);

namespace DoctrineMigrations;

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
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE `blog_tags` DROP FOREIGN KEY `FK_8F6C18B6ADF5624F`; ALTER TABLE `blog_tags` '.
            'ADD CONSTRAINT `FK_8F6C18B6ADF5624F` FOREIGN KEY (`tag_fk`) REFERENCES `tags`(`id`) ON DELETE CASCADE '.
            'ON UPDATE CASCADE; ALTER TABLE `blog_tags` DROP FOREIGN KEY `FK_8F6C18B6CDC77FC9`; '.
            'ALTER TABLE `blog_tags` ADD CONSTRAINT `FK_8F6C18B6CDC77FC9` FOREIGN KEY (`blog_fk`) REFERENCES '.
            '`blog`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE `blog_tags` DROP FOREIGN KEY `FK_8F6C18B6ADF5624F`; ALTER TABLE `blog_tags` '.
            'ADD CONSTRAINT `FK_8F6C18B6ADF5624F` FOREIGN KEY (`tag_fk`) REFERENCES `tags`(`id`) ON DELETE RESTRICT '.
            'ON UPDATE RESTRICT; ALTER TABLE `blog_tags` DROP FOREIGN KEY `FK_8F6C18B6CDC77FC9`; '.
            'ALTER TABLE `blog_tags` ADD CONSTRAINT `FK_8F6C18B6CDC77FC9` FOREIGN KEY (`blog_fk`) REFERENCES '.
            '`blog`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;');
    }
}
