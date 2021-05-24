<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815083524 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'CREATE TABLE blog_tags';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE `blog_tags` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `blog_fk` int(11) unsigned NOT NULL,
            `tag_fk` int(11) unsigned NOT NULL,
            `created` datetime NOT NULL,
            `creator` int(11) unsigned NOT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `un_blog_tag` (`blog_fk`,`tag_fk`),
            KEY `fk_blog_tag_creator_fk` (`creator`),
            KEY `fk_blog_tag_modifier_fk` (`modifier`),
            KEY `fk_blog_tag_tag_fk` (`tag_fk`),
            CONSTRAINT `FK_8F6C18B6ADF5624F` FOREIGN KEY (`tag_fk`) REFERENCES `tags` (`id`),
            CONSTRAINT `FK_8F6C18B6CDC77FC9` FOREIGN KEY (`blog_fk`) REFERENCES `blog` (`id`),
            CONSTRAINT `fk_blog_tag_creator_fk` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON DELETE '.
                'RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `fk_blog_tag_modifier_fk` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON DELETE '.
                'RESTRICT ON UPDATE RESTRICT
           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE blog_tags');
    }
}
