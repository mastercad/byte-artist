<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815082849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'CREATE TABLE blog_group_blog';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `blog_group_blog` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `blog_fk` int(11) unsigned NOT NULL,
            `group_fk` int(11) unsigned NOT NULL,
            `ordering` int(11) NOT NULL,
            `created` datetime NOT NULL,
            `creator` int(11) unsigned NOT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `fk_blog_group_blog_fk` (`blog_fk`),
            KEY `fk_blog_group_blog_group_fk` (`group_fk`),
            KEY `fk_blog_group_creator` (`creator`),
            KEY `fk_blog_group_modifier` (`modifier`),
            CONSTRAINT `FK_F8928DAABBFD9FD` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`),
            CONSTRAINT `FK_F8928DABC06EA63` FOREIGN KEY (`creator`) REFERENCES `user` (`id`)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        /* Schema */
        /*
        $this->addSql('CREATE TABLE `blog_group_blog` (
            `id` int(11) NOT NULL,
            `blog_fk` int(11) UNSIGNED NOT NULL,
            `group_fk` int(11) UNSIGNED NOT NULL,
            `ordering` int(11) NOT NULL,
            `created` datetime NOT NULL,
            `creator` int(11) UNSIGNED NOT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) UNSIGNED DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        */
        /* Indizes */
        /*
        $this->addSql('ALTER TABLE `blog_group_blog`
            ADD PRIMARY KEY (`id`),
            ADD KEY `fk_blog_group_creator` (`creator`),
            ADD KEY `fk_blog_group_modifier` (`modifier`),
            ADD KEY `fk_blog_group_blog_fk` (`blog_fk`),
            ADD KEY `fk_blog_group_blog_group_fk` (`group_fk`);');
        */
        /* AUTO_INCREMENT */
        /*
        $this->addSql('ALTER TABLE `blog_group_blog`
            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
        COMMIT;');
        */
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE blog_group_blog');
    }
}
