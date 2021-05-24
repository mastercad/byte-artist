<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815083124 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'CREATE TABLE blog_comments';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE `blog_comments` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `user_name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `user_email` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `parent_fk` int(11) unsigned DEFAULT NULL,
            `blog_fk` int(11) unsigned NOT NULL,
            `created` datetime NOT NULL,
            `creator` int(11) unsigned NOT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `fk_blog_comment_creator` (`creator`),
            KEY `fk_blog_comment_modifier` (`modifier`),
            KEY `fk_blog_comment_blog_fk` (`blog_fk`),
            KEY `fk_blog_comment_parent_fk` (`parent_fk`),
            CONSTRAINT `FK_2BC3B20D655DCB2E` FOREIGN KEY (`parent_fk`) REFERENCES `blog_comments` (`id`),
            CONSTRAINT `FK_2BC3B20DCDC77FC9` FOREIGN KEY (`blog_fk`) REFERENCES `blog` (`id`),
            CONSTRAINT `fk_blog_comment_creator` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON DELETE '.
                'RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `fk_blog_comment_modifier` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON '.
                'DELETE RESTRICT ON UPDATE RESTRICT
           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE blog_comments');
    }
}
