<?php

declare(strict_types=1);

namespace App\Tests\Unit\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use DoctrineMigrations\Version20190730190842;
use DoctrineMigrations\Version20190807114252;
use DoctrineMigrations\Version20190807115636;
use DoctrineMigrations\Version20190815080521;
use DoctrineMigrations\Version20190815081144;
use DoctrineMigrations\Version20190815081744;
use DoctrineMigrations\Version20190815082050;
use DoctrineMigrations\Version20190815082416;
use DoctrineMigrations\Version20190815082645;
use DoctrineMigrations\Version20190815082849;
use DoctrineMigrations\Version20190815083124;
use DoctrineMigrations\Version20190815083319;
use DoctrineMigrations\Version20190815083524;
use DoctrineMigrations\Version20210513192513;
use DoctrineMigrations\Version20210513192857;
use DoctrineMigrations\Version20260409120000;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Verifies that all migrations are idempotent:
 *  - CREATE TABLE statements use IF NOT EXISTS
 *  - DROP TABLE statements use IF EXISTS
 *  - INSERT seed data uses WHERE NOT EXISTS
 *  - FK-constraint changes are guarded by INFORMATION_SCHEMA lookups
 *  - ADD COLUMN / DROP COLUMN are guarded by INFORMATION_SCHEMA lookups
 */
class MigrationTest extends TestCase
{
    // ------------------------------------------------------------------ helpers

    /** @return Connection&MockObject */
    private function makeConnection(mixed $fetchOneReturn = '0'): Connection
    {
        $schemaManager = $this->createMock(AbstractSchemaManager::class);

        /** @var Connection&MockObject $conn */
        $conn = $this->createMock(Connection::class);
        $conn->method('getDatabasePlatform')->willReturn(new MySQLPlatform());
        $conn->method('createSchemaManager')->willReturn($schemaManager);
        $conn->method('fetchOne')->willReturn($fetchOneReturn);

        return $conn;
    }

    /** @return list<string> */
    private function upSql(AbstractMigration $migration): array
    {
        $migration->up(new Schema());

        return array_map(
            static fn ($q) => $q->getStatement(),
            $migration->getSql()
        );
    }

    /** @return list<string> */
    private function downSql(AbstractMigration $migration): array
    {
        $migration->down(new Schema());

        return array_map(
            static fn ($q) => $q->getStatement(),
            $migration->getSql()
        );
    }

    // ------------------------------------------------------------------ Version20190730190842

    public function testVersion20190730190842DescriptionIsNotEmpty(): void
    {
        $m = new Version20190730190842($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190730190842UpUsesCreateIfNotExists(): void
    {
        $m = new Version20190730190842($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $sql);
    }

    public function testVersion20190730190842DownUsesDropIfExists(): void
    {
        $m = new Version20190730190842($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    // ------------------------------------------------------------------ Version20190807114252

    public function testVersion20190807114252DescriptionIsNotEmpty(): void
    {
        $m = new Version20190807114252($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190807114252UpUsesWhereNotExists(): void
    {
        $m = new Version20190807114252($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('WHERE NOT EXISTS', $sql);
    }

    public function testVersion20190807114252DownDeletesSystemUser(): void
    {
        $m = new Version20190807114252($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DELETE', $sql);
        self::assertStringContainsString('user', $sql);
    }

    // ------------------------------------------------------------------ Version20190807115636

    public function testVersion20190807115636DescriptionIsNotEmpty(): void
    {
        $m = new Version20190807115636($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190807115636UpUsesCreateIfNotExists(): void
    {
        $m = new Version20190807115636($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $sql);
        self::assertStringContainsString('tags', $sql);
    }

    public function testVersion20190807115636DownUsesDropIfExists(): void
    {
        $m = new Version20190807115636($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    // ------------------------------------------------------------------ Version20190815080521

    public function testVersion20190815080521DescriptionIsNotEmpty(): void
    {
        $m = new Version20190815080521($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190815080521UpCreatesTableIfNotExists(): void
    {
        $m = new Version20190815080521($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $sql);
        self::assertStringContainsString('project_states', $sql);
    }

    public function testVersion20190815080521UpInsertsWithWhereNotExists(): void
    {
        $m = new Version20190815080521($this->makeConnection(), new NullLogger());
        $sql = implode("\n", $this->upSql($m));

        self::assertStringContainsString('WHERE NOT EXISTS', $sql);
        self::assertSame(3, substr_count($sql, 'WHERE NOT EXISTS'), 'Expected 3 idempotent INSERT statements');
    }

    public function testVersion20190815080521DownUsesDropIfExists(): void
    {
        $m = new Version20190815080521($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    // ------------------------------------------------------------------ Version20190815081144

    public function testVersion20190815081144DescriptionIsNotEmpty(): void
    {
        $m = new Version20190815081144($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190815081144UpCreatesProjectsIfNotExists(): void
    {
        $m = new Version20190815081144($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $sql);
        self::assertStringContainsString('projects', $sql);
    }

    public function testVersion20190815081144DownUsesDropIfExists(): void
    {
        $m = new Version20190815081144($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    // ------------------------------------------------------------------ Version20190815081744

    public function testVersion20190815081744DescriptionIsNotEmpty(): void
    {
        $m = new Version20190815081744($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190815081744UpCreatesProjectCommentsIfNotExists(): void
    {
        $m = new Version20190815081744($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $sql);
        self::assertStringContainsString('project_comments', $sql);
    }

    public function testVersion20190815081744DownUsesDropIfExists(): void
    {
        $m = new Version20190815081744($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    // ------------------------------------------------------------------ Version20190815082050

    public function testVersion20190815082050DescriptionIsNotEmpty(): void
    {
        $m = new Version20190815082050($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190815082050UpCreatesProjectTagsIfNotExists(): void
    {
        $m = new Version20190815082050($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $sql);
        self::assertStringContainsString('project_tags', $sql);
    }

    public function testVersion20190815082050DownUsesDropIfExists(): void
    {
        $m = new Version20190815082050($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    // ------------------------------------------------------------------ Version20190815082416

    public function testVersion20190815082416DescriptionIsNotEmpty(): void
    {
        $m = new Version20190815082416($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190815082416UpCreatesBlogGroupsIfNotExists(): void
    {
        $m = new Version20190815082416($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $sql);
        self::assertStringContainsString('blog_groups', $sql);
    }

    public function testVersion20190815082416DownUsesDropIfExists(): void
    {
        $m = new Version20190815082416($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    // ------------------------------------------------------------------ Version20190815082645

    public function testVersion20190815082645DescriptionIsNotEmpty(): void
    {
        $m = new Version20190815082645($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190815082645UpCreatesBlogIfNotExists(): void
    {
        $m = new Version20190815082645($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $sql);
        self::assertStringContainsString('blog', $sql);
    }

    public function testVersion20190815082645DownUsesDropIfExists(): void
    {
        $m = new Version20190815082645($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    // ------------------------------------------------------------------ Version20190815082849

    public function testVersion20190815082849DescriptionIsNotEmpty(): void
    {
        $m = new Version20190815082849($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190815082849UpCreatesBlogGroupBlogIfNotExists(): void
    {
        $m = new Version20190815082849($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $sql);
        self::assertStringContainsString('blog_group_blog', $sql);
    }

    public function testVersion20190815082849DownUsesDropIfExists(): void
    {
        $m = new Version20190815082849($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    // ------------------------------------------------------------------ Version20190815083124

    public function testVersion20190815083124DescriptionIsNotEmpty(): void
    {
        $m = new Version20190815083124($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190815083124UpCreatesBlogCommentsIfNotExists(): void
    {
        $m = new Version20190815083124($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $sql);
        self::assertStringContainsString('blog_comments', $sql);
    }

    public function testVersion20190815083124DownUsesDropIfExists(): void
    {
        $m = new Version20190815083124($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    // ------------------------------------------------------------------ Version20190815083319

    public function testVersion20190815083319DescriptionIsNotEmpty(): void
    {
        $m = new Version20190815083319($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190815083319UpCreatesBlogSubscribersIfNotExists(): void
    {
        $m = new Version20190815083319($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $sql);
        self::assertStringContainsString('blog_subscribers', $sql);
    }

    public function testVersion20190815083319DownUsesDropIfExists(): void
    {
        $m = new Version20190815083319($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    // ------------------------------------------------------------------ Version20190815083524

    public function testVersion20190815083524DescriptionIsNotEmpty(): void
    {
        $m = new Version20190815083524($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20190815083524UpCreatesBlogTagsIfNotExists(): void
    {
        $m = new Version20190815083524($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS', $sql);
        self::assertStringContainsString('blog_tags', $sql);
    }

    public function testVersion20190815083524DownUsesDropIfExists(): void
    {
        $m = new Version20190815083524($this->makeConnection(), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    // ------------------------------------------------------------------ Version20210513192513

    public function testVersion20210513192513DescriptionIsNotEmpty(): void
    {
        $m = new Version20210513192513($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20210513192513UpDropsFkWhenConstraintExists(): void
    {
        // fetchOne returns '1' → FK exists → DROP + ADD both expected
        $m = new Version20210513192513($this->makeConnection('1'), new NullLogger());
        $sql = implode("\n", $this->upSql($m));

        self::assertStringContainsString('DROP FOREIGN KEY `FK_562D5C3E14A1EC2`', $sql);
        self::assertStringContainsString('ADD CONSTRAINT `FK_562D5C3E14A1EC2`', $sql);
        self::assertStringContainsString('DROP FOREIGN KEY `FK_562D5C3EADF5624F`', $sql);
        self::assertStringContainsString('ADD CONSTRAINT `FK_562D5C3EADF5624F`', $sql);
        self::assertStringContainsString('ON DELETE CASCADE', $sql);
    }

    public function testVersion20210513192513UpSkipsDropWhenConstraintAbsent(): void
    {
        // fetchOne returns '0' → FK absent → only ADD expected, no DROP
        $m = new Version20210513192513($this->makeConnection('0'), new NullLogger());
        $sql = implode("\n", $this->upSql($m));

        self::assertStringNotContainsString('DROP FOREIGN KEY', $sql);
        self::assertStringContainsString('ADD CONSTRAINT `FK_562D5C3E14A1EC2`', $sql);
        self::assertStringContainsString('ADD CONSTRAINT `FK_562D5C3EADF5624F`', $sql);
        self::assertStringContainsString('ON DELETE CASCADE', $sql);
    }

    public function testVersion20210513192513DownRestoresRestrictBehaviour(): void
    {
        $m = new Version20210513192513($this->makeConnection('1'), new NullLogger());
        $sql = implode("\n", $this->downSql($m));

        self::assertStringContainsString('ON DELETE RESTRICT', $sql);
        self::assertStringContainsString('ON UPDATE RESTRICT', $sql);
    }

    // ------------------------------------------------------------------ Version20210513192857

    public function testVersion20210513192857DescriptionIsNotEmpty(): void
    {
        $m = new Version20210513192857($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20210513192857UpDropsFkWhenConstraintExists(): void
    {
        $m = new Version20210513192857($this->makeConnection('1'), new NullLogger());
        $sql = implode("\n", $this->upSql($m));

        self::assertStringContainsString('DROP FOREIGN KEY `FK_8F6C18B6ADF5624F`', $sql);
        self::assertStringContainsString('ADD CONSTRAINT `FK_8F6C18B6ADF5624F`', $sql);
        self::assertStringContainsString('DROP FOREIGN KEY `FK_8F6C18B6CDC77FC9`', $sql);
        self::assertStringContainsString('ADD CONSTRAINT `FK_8F6C18B6CDC77FC9`', $sql);
        self::assertStringContainsString('ON DELETE CASCADE', $sql);
    }

    public function testVersion20210513192857UpSkipsDropWhenConstraintAbsent(): void
    {
        $m = new Version20210513192857($this->makeConnection('0'), new NullLogger());
        $sql = implode("\n", $this->upSql($m));

        self::assertStringNotContainsString('DROP FOREIGN KEY', $sql);
        self::assertStringContainsString('ADD CONSTRAINT `FK_8F6C18B6ADF5624F`', $sql);
        self::assertStringContainsString('ADD CONSTRAINT `FK_8F6C18B6CDC77FC9`', $sql);
    }

    public function testVersion20210513192857DownRestoresRestrictBehaviour(): void
    {
        $m = new Version20210513192857($this->makeConnection('1'), new NullLogger());
        $sql = implode("\n", $this->downSql($m));

        self::assertStringContainsString('ON DELETE RESTRICT', $sql);
        self::assertStringContainsString('ON UPDATE RESTRICT', $sql);
    }

    // ------------------------------------------------------------------ Version20260409120000

    public function testVersion20260409120000DescriptionIsNotEmpty(): void
    {
        $m = new Version20260409120000($this->makeConnection(), new NullLogger());
        self::assertNotEmpty($m->getDescription());
    }

    public function testVersion20260409120000UpAddsColumnWhenAbsent(): void
    {
        // fetchOne returns '0' → column absent → ADD COLUMN expected
        $m = new Version20260409120000($this->makeConnection('0'), new NullLogger());
        $sql = implode(' ', $this->upSql($m));

        self::assertStringContainsString('ADD COLUMN `is_public`', $sql);
    }

    public function testVersion20260409120000UpSkipsWhenColumnExists(): void
    {
        // fetchOne returns '1' → column already present → no SQL
        $m = new Version20260409120000($this->makeConnection('1'), new NullLogger());
        $statements = $this->upSql($m);

        self::assertEmpty($statements, 'up() must not emit SQL when is_public column already exists');
    }

    public function testVersion20260409120000DownDropsColumnWhenPresent(): void
    {
        $m = new Version20260409120000($this->makeConnection('1'), new NullLogger());
        $sql = implode(' ', $this->downSql($m));

        self::assertStringContainsString('DROP COLUMN `is_public`', $sql);
    }

    public function testVersion20260409120000DownSkipsWhenColumnAbsent(): void
    {
        $m = new Version20260409120000($this->makeConnection('0'), new NullLogger());
        $statements = $this->downSql($m);

        self::assertEmpty($statements, 'down() must not emit SQL when is_public column is already absent');
    }
}
