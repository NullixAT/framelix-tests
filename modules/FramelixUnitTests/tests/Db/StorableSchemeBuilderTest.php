<?php

namespace Db;

use Framelix\Framelix\Db\Mysql;
use Framelix\Framelix\Db\MysqlStorableSchemeBuilder;
use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Storable\Storable;
use Framelix\FramelixUnitTests\Storable\TestStorable2;
use Framelix\FramelixUnitTests\StorableException\TestStorableNoType;
use Framelix\FramelixUnitTests\StorableException\TestStorableUnsupportedType;
use Framelix\FramelixUnitTests\TestCase;
use Throwable;

final class StorableSchemeBuilderTest extends TestCase
{
    /**
     * Drop everything before starting the tests
     */
    public static function setUpBeforeClass(): void
    {
        $db = Mysql::get('test');
        $db->query("DROP DATABASE `{$db->connectionConfig['database']}`");
        $db->query("CREATE DATABASE `{$db->connectionConfig['database']}`");
        $db->query("USE `{$db->connectionConfig['database']}`");
    }

    public function testBuilderQueries(): void
    {
        $db = Mysql::get('test');
        $schema = Storable::getStorableSchema(TestStorable2::class);
        // assert exact same schema (cached already)
        $this->assertSame($schema, Storable::getStorableSchema(TestStorable2::class));
        $builder = new MysqlStorableSchemeBuilder($db);
        // first create all things
        $queries = $builder->getQueries();
        // all new queries that do not modify anything are considered safe
        $this->assertCount(count($queries), $builder->getSafeQueries());
        foreach ($queries as $queryData) {
            $db->query($queryData['query']);
        }
        // calling the builder immediately after should not need to change anything
        $queries = $builder->getQueries();
        $this->assertQueryCount(0, $queries, true);
        // deleting a column and than the builder should recreate this including the index
        // 3 queries because 1x adding, 1x reordering columns and 1x creating an index
        $db->query("ALTER TABLE framelix_framelixunittests_storable_teststorable2 DROP COLUMN `createUser`");
        $queries = $builder->getQueries();
        // 1 of 3 is unsafe, so we have 2 safe queries
        $this->assertCount(2, $builder->getSafeQueries());
        // when having safe queries, there couldnt be any unsafe queries
        // as safe queries always need to be executed prior to generate unsafe queries correctly
        $this->assertCount(0, $builder->getUnsafeQueries());
        $this->assertQueryCount(3, $queries, true);
        // modifying some table data to simulate changed property behaviour
        $db->query(
            'ALTER TABLE `framelix_framelixunittests_storable_teststorable2`
	CHANGE COLUMN `createTime` `createTime` DATE NULL DEFAULT NULL AFTER `id`,
	CHANGE COLUMN `longText` `longText` VARCHAR(50) NULL DEFAULT NULL COLLATE \'utf8mb4_unicode_ci\' AFTER `name`,
	CHANGE COLUMN `selfReferenceOptional` `selfReferenceOptionals` BIGINT(18) UNSIGNED NULL DEFAULT NULL,
	DROP INDEX `selfReferenceOptional`'
        );

        $queries = $builder->getQueries();
        $this->assertQueryCount(6, $queries, true);

        // calling the builder immediately after should not need to change anything
        $queries = $builder->getQueries();
        $this->assertQueryCount(0, $queries, true);
        // droping an index and let the system recreate it
        $db->query("ALTER TABLE `framelix_framelix_storable_user` DROP INDEX `updateUser`");
        $queries = $builder->getQueries();
        $this->assertQueryCount(1, $queries, true);
        // adding some additional obsolete columns and tables that the builder should delete
        $db->query(
            "ALTER TABLE `framelix_framelix_storable_user`
	ADD COLUMN `unusedTime` DATETIME NULL DEFAULT NULL,
	ADD INDEX `flagLocked` (`flagLocked`)"
        );
        $db->query('CREATE TABLE `framelix_unused_table` (`id` INT(11) NULL DEFAULT NULL)');
        // 3rd party tables are untouched by default
        $db->query('CREATE TABLE `unused_table` (`id` INT(11) NULL DEFAULT NULL)');
        // altering/deleting a existing column/table or is always unsafe
        $queries = $builder->getSafeQueries();
        $this->assertCount(0, $queries);
        $queries = $builder->getUnsafeQueries();
        $this->assertCount(3, $queries);
        $queries = $builder->getQueries();
        $this->assertQueryCount(3, $queries, true);
    }

    public function testUnsupportedDbPropertyType(): void
    {
        $e = null;
        try {
            Storable::getStorableSchema(TestStorableUnsupportedType::class);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::STORABLESCHEMA_INVALID_DOUBLE, $e);
    }

    public function testNoDbPropertyType(): void
    {
        $e = null;
        try {
            Storable::getStorableSchema(TestStorableNoType::class);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::STORABLESCHEMA_INVALID_PROPERTY_TYPE, $e);
    }

    /**
     * Assert special query count which ignores some irrelevant queries
     * @param int $count
     * @param array $queries
     * @param bool $execute Execute queries after assert
     */
    private function assertQueryCount(int $count, array $queries, bool $execute): void
    {
        foreach ($queries as $key => $row) {
            // insert metas are ignored, as they are always here
            if ($row['type'] === 'insert-meta') {
                unset($queries[$key]);
            }
        }
        $this->assertCount($count, $queries);
        if ($execute) {
            foreach ($queries as $queryData) {
                Mysql::get('test')->query($queryData['query']);
            }
        }
    }
}