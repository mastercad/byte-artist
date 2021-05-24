<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190807115636 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Tags Table';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("CREATE TABLE `tags` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `seo_link` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `parent_fk` int(11) unsigned DEFAULT NULL,
            `created` datetime NOT NULL,
            `creator` int(11) unsigned NOT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `UNIQ_6FBC94265E237E06` (`name`),
            KEY `fk_tag_creator` (`creator`),
            KEY `fk_tag_modifier` (`modifier`),
            KEY `fk_tag_parent_fk` (`parent_fk`),
            CONSTRAINT `FK_6FBC9426655DCB2E` FOREIGN KEY (`parent_fk`) REFERENCES `tags` (`id`),
            CONSTRAINT `fk_tag_creator` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `fk_tag_modifier` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DROP TABLE `tags`");
    }
}
