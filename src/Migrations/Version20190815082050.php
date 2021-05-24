<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815082050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'CREATE TABLE project_tags';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `project_tags` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `project_fk` int(11) unsigned NOT NULL,
            `tag_fk` int(11) unsigned NOT NULL,
            `created` datetime NOT NULL,
            `creator` int(11) unsigned NOT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `fk_project_creator_fk` (`creator`),
            KEY `fk_project_modifier_fk` (`modifier`),
            KEY `fk_project_tags_project_fk` (`project_fk`),
            KEY `fk_project_tags_tag_fk` (`tag_fk`),
            CONSTRAINT `FK_562D5C3E14A1EC2` FOREIGN KEY (`project_fk`) REFERENCES `projects` (`id`),
            CONSTRAINT `FK_562D5C3EADF5624F` FOREIGN KEY (`tag_fk`) REFERENCES `tags` (`id`),
            CONSTRAINT `fk_project_creator_fk` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON DELETE RESTRICT '.
                'ON UPDATE RESTRICT,
            CONSTRAINT `fk_project_modifier_fk` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON DELETE RESTRICT '.
                'ON UPDATE RESTRICT
           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE project_tags');
    }
}
