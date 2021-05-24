<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815081144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'CREATE TABLE projects';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `projects` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `short_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            `preview_picture` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            `link` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `seo_link` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `is_public` tinyint(1) NOT NULL,
            `state_fk` int(11) unsigned DEFAULT NULL,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `original_link` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `creator` int(11) unsigned NOT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `fk_project_modifier` (`modifier`),
            KEY `fk_project_creator` (`creator`),
            KEY `fk_project_state` (`state_fk`),
            CONSTRAINT `FK_5C93B3A412FF3D9F` FOREIGN KEY (`state_fk`) REFERENCES `project_states` (`id`),
            CONSTRAINT `fk_project_creator` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON '.
                'UPDATE RESTRICT,
            CONSTRAINT `fk_project_modifier` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON '.
                'UPDATE RESTRICT
           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE projects');
    }
}
