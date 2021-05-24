<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815082645 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'CREATE TABLE blog';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE `blog` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `seo_link` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `short_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `preview_picture` varchar(250) DEFAULT NULL,
            `group_fk` int(11) unsigned DEFAULT NULL,
            `group_order` int(11) DEFAULT NULL,
            `created` datetime NOT NULL,
            `creator` int(11) unsigned NOT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `fk_blog_creator` (`creator`),
            KEY `fk_blog_modifier` (`modifier`),
            KEY `fk_blog_group_fk` (`group_fk`),
            CONSTRAINT `FK_C0155143E973D819` FOREIGN KEY (`group_fk`) REFERENCES `blog_groups` (`id`),
            CONSTRAINT `fk_blog_creator` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON '.
                'UPDATE RESTRICT,
            CONSTRAINT `fk_blog_modifier` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON '.
                'UPDATE RESTRICT
           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE blog');
    }
}
