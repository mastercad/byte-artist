<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190807114252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'CREATE SYSTEM USER';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `user` (`id`, `username`, `roles`, `password`, `email`) VALUES ('1', 'SYSTEM', ".
            "'{\"role\":\"ROLE_SUPER_ADMIN\"}', '', '');");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE `user` FROM `user` WHERE `id` = 1');
    }
}
