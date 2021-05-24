<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815080521 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create project_states Table';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE `project_states` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `seo_link` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `created` datetime NOT NULL,
            `creator` int(10) unsigned DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `fk_project_state_creator_fk` (`creator`),
            KEY `fk_project_state_modifier_fk` (`modifier`),
            CONSTRAINT `fk_project_state_creator_fk` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `fk_project_state_modifier_fk` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');

        $this->addSql("INSERT INTO `project_states` (`id`, `name`, `seo_link`, `description`, `created`, `creator`, `modified`, `modifier`) VALUES
            (1, 'In Planung', 'in-planung', 'Unter \"In Planung\" liegt alles, was ich in absehbarer Zeit oder auch in weiter Ferne in Planung habe. Diese Projekte sind sortiert nach dem Eintragdatum, absteigend.', '".date("Y-m-d H:i:s")."', 1, NULL, NULL),
            (2, 'In Arbeit', 'in-arbeit', 'Hier sind alle Projekte festgehalten, die ich aktuell in Arbeit habe. Sortiert sind die Projekte absteigend nach dem Eintragdatum', '".date("Y-m-d H:i:s")."', 1, NULL, NULL),
            (3, 'Abgeschlossen', 'abgeschlossen', 'Hier finden Sie alle Projekte die ich bereits abgeschlossen habe, sortiert nach dem Abschlußdatum absteigend.', '".date("Y-m-d H:i:s")."', 1, NULL, NULL);");

        /** Structure */
        /*
        $this->addSql('CREATE TABLE `project_states` (
            `id` int(11) UNSIGNED NOT NULL,
            `name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `seo_link` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `created` datetime NOT NULL,
            `creator` int(11) UNSIGNED NOT NULL,
            `modified` datetime DEFAULT NULL,
            `modifier` int(11) UNSIGNED DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        */
        /** Data */
        /*
        $this->addSql("INSERT INTO `project_states` (`id`, `name`, `seo_link`, `description`, `created`, `creator`, `modified`, `modifier`) VALUES
            (1, 'In Planung', 'in-planung', 'Unter \"In Planung\" liegt alles, was ich in absehbarer Zeit oder auch in weiter Ferne in Planung habe. Diese Projekte sind sortiert nach dem Eintragdatum, absteigend.', '".date("Y-m-d H:i:s")."', 1, NULL, NULL),
            (2, 'In Arbeit', 'in-arbeit', 'Hier sind alle Projekte festgehalten, die ich aktuell in Arbeit habe. Sortiert sind die Projekte absteigend nach dem Eintragdatum', '".date("Y-m-d H:i:s")."', 1, NULL, NULL),
            (3, 'Abgeschlossen', 'abgeschlossen', 'Hier finden Sie alle Projekte die ich bereits abgeschlossen habe, sortiert nach dem Abschlußdatum absteigend.', '".date("Y-m-d H:i:s")."', 1, NULL, NULL);");
        */
        /** Indizies */
        /*
        $this->addSql('ALTER TABLE `project_states`
            ADD PRIMARY KEY (`id`),
            ADD KEY `fk_project_state_creator_fk` (`creator`),
            ADD KEY `fk_project_state_modifier_fk` (`modifier`);');
        */
        /** Autoincrement */
        /*
        $this->addSql('ALTER TABLE `project_states`
            MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;');
        */
        /** Constraints */
        /*
        $this->addSql('ALTER TABLE `project_states`
            ADD CONSTRAINT `fk_project_state_creator_fk` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
            ADD CONSTRAINT `fk_project_state_modifier_fk` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
            COMMIT;');
        */
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE project_states');
    }
}
