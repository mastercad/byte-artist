<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815083319 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'CREATE TABLE blog_subscribers';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE `blog_subscribers` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `blog_fk` int(11) unsigned NOT NULL,
            `user_fk` int(11) unsigned NOT NULL,
            `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `creator` int(11) unsigned NOT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `fk_blog_subscriber_creator_fk` (`creator`),
            KEY `fk_blog_subscriber_modifier_fk` (`modifier`),
            KEY `fk_blog_subscriber_blog_fk` (`blog_fk`),
            KEY `fk_blog_subscriber_user_fk` (`user_fk`),
            CONSTRAINT `FK_F6FD8A52B049D2CB` FOREIGN KEY (`user_fk`) REFERENCES `user` (`id`),
            CONSTRAINT `FK_F6FD8A52CDC77FC9` FOREIGN KEY (`blog_fk`) REFERENCES `blog` (`id`),
            CONSTRAINT `fk_blog_subscriber_creator_fk` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON '.
                'DELETE RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `fk_blog_subscriber_modifier_fk` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON '.
                'DELETE RESTRICT ON UPDATE RESTRICT
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE blog_subscribers');
    }
}
