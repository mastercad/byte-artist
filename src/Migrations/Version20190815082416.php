<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815082416 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'CREATE TABLE blog_groups';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE `blog_groups` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `seo_link` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `created` datetime NOT NULL,
            `creator` int(11) unsigned NOT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `fk_blog_group_creator_fk` (`creator`),
            KEY `fk_blog_group_modifier_fk` (`modifier`),
            CONSTRAINT `fk_blog_group_creator_fk` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `fk_blog_group_modifier_fk` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        /** Schema */
        /*
        $this->addSql('CREATE TABLE `blog_groups` (
            `id` int(11) UNSIGNED NOT NULL,
            `name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `seo_link` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `created` datetime NOT NULL,
            `creator` int(11) UNSIGNED NOT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) UNSIGNED DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        */
        /** Indizies */
        /*
        $this->addSql('ALTER TABLE `blog_groups`
            ADD PRIMARY KEY (`id`),
            ADD KEY `fk_blog_group_creator_fk` (`creator`),
            ADD KEY `fk_blog_group_modifier_fk` (`modifier`);');
        */
        /** AUTO_INCREMENT */
        /*
        $this->addSql('ALTER TABLE `blog_groups`
            MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;');
        */
        /** Constraints */
        /*
        $this->addSql('ALTER TABLE `blog_groups`
            ADD CONSTRAINT `fk_blog_group_creator_fk` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
            ADD CONSTRAINT `fk_blog_group_modifier_fk` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
        COMMIT;');
        */
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE blog_groups');
    }
}
