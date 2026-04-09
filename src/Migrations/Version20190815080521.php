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
    public function getDescription(): string
    {
        return 'Create project_states Table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS `project_states` (
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
            CONSTRAINT `fk_project_state_creator_fk` FOREIGN KEY (`creator`) REFERENCES `user` (`id`) ON DELETE '.
                'RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `fk_project_state_modifier_fk` FOREIGN KEY (`modifier`) REFERENCES `user` (`id`) ON DELETE '.
                'RESTRICT ON UPDATE RESTRICT
           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');

        $this->addSql('INSERT INTO `project_states` (`id`, `name`, `seo_link`, `description`, `created`, `creator`, '.
            "`modified`, `modifier`)\n            SELECT * FROM (SELECT 1 AS id, 'In Planung' AS name, 'in-planung' AS seo_link,".
                " 'Unter \"In Planung\" liegt alles, was ich in absehbarer Zeit oder auch in weiter Ferne in Planung habe.".
                " Diese Projekte sind sortiert nach dem Eintragdatum, absteigend.' AS description,".
                " '".date('Y-m-d H:i:s')."' AS created, 1 AS creator, NULL AS modified, NULL AS modifier) AS tmp\n".
                "            WHERE NOT EXISTS (SELECT 1 FROM `project_states` WHERE `id` = 1);\n".
            "INSERT INTO `project_states` (`id`, `name`, `seo_link`, `description`, `created`, `creator`, `modified`, `modifier`)\n".
                "            SELECT * FROM (SELECT 2 AS id, 'In Arbeit' AS name, 'in-arbeit' AS seo_link,".
                " 'Hier sind alle Projekte festgehalten, die ich aktuell in Arbeit habe.".
                " Sortiert sind die Projekte absteigend nach dem Eintragdatum' AS description,".
                " '".date('Y-m-d H:i:s')."' AS created, 1 AS creator, NULL AS modified, NULL AS modifier) AS tmp\n".
                "            WHERE NOT EXISTS (SELECT 1 FROM `project_states` WHERE `id` = 2);\n".
            "INSERT INTO `project_states` (`id`, `name`, `seo_link`, `description`, `created`, `creator`, `modified`, `modifier`)\n".
                "            SELECT * FROM (SELECT 3 AS id, 'Abgeschlossen' AS name, 'abgeschlossen' AS seo_link,".
                " 'Hier finden Sie alle Projekte die ich bereits abgeschlossen habe,".
                " sortiert nach dem Abschlußdatum absteigend.' AS description,".
                " '".date('Y-m-d H:i:s')."' AS created, 1 AS creator, NULL AS modified, NULL AS modifier) AS tmp\n".
                '            WHERE NOT EXISTS (SELECT 1 FROM `project_states` WHERE `id` = 3)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS project_states');
    }
}
