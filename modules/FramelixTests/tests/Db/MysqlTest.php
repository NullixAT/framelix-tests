<?php

namespace Db;

use Framelix\Framelix\Config;
use Framelix\Framelix\DateTime;
use Framelix\Framelix\Db\Mysql;
use Framelix\Framelix\ErrorCode;
use Framelix\FramelixTests\Storable\TestStorable2;
use Framelix\FramelixTests\TestCase;
use ReflectionClass;
use Throwable;

use function fopen;

final class MysqlTest extends TestCase
{

    public function testCreate(): void
    {
        $this->expectNotToPerformAssertions();
        Mysql::get('test')->query("DROP TABLE IF EXISTS `dev`");
    }

    /**
     * @depends testCreate
     */
    public function testExceptionConnectError()
    {
        $e = null;
        try {
            $configKey = 'database[test]';
            // connect error simulate with wrong password
            Config::set($configKey . "[passwordOld]", Config::get($configKey . "[password]"));
            Config::set($configKey . "[password]", "=!'§$%&%&(&/(/&(");
            Mysql::get('test');
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::MYSQL_CONNECT_ERROR, $e);
    }

    /**
     * @depends testExceptionConnectError
     */
    public function testQueries(): void
    {
        Mysql::$logExecutedQueries = true;
        $db = Mysql::get('test');

        // connect does nothing when already connected
        $db->connect();

        // make sure there is only one instance of a db connection
        $this->assertSame($db, Mysql::get('test'));

        // create dev table
        $this->assertTrue(
            $db->query(
                "CREATE TABLE `dev` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `text` LONGTEXT NULL COLLATE 'utf8mb4_unicode_ci',
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_unicode_ci'
            ENGINE=InnoDB"
            )
        );
        $this->assertCount(1, $db->executedQueries);
        // create storable table
        $this->assertTrue(
            $db->query(
                "CREATE TABLE IF NOT EXISTS `" . TestStorable2::class . "` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `text` LONGTEXT NULL COLLATE 'utf8mb4_unicode_ci',
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_unicode_ci'
            ENGINE=InnoDB"
            )
        );

        // insert dev text
        $testText = "foobar\"quote\"";
        $testText2 = "foobar\"quote\"2";
        $this->assertTrue(
            $db->insert("dev", ['text' => $testText])
        );

        // check different select fetch formats
        $this->assertEquals(1, $db->getLastInsertId());
        $this->assertEquals(1, $db->getAffectedRows());
        $this->assertEquals($testText, $db->fetchOne("SELECT text FROM dev"));
        $this->assertEquals([$testText], $db->fetchColumn("SELECT text FROM dev"));
        $this->assertEquals([$testText => $testText], $db->fetchColumn("SELECT text, text FROM dev"));
        $this->assertEquals([1 => ["id" => 1, "text" => $testText]], $db->fetchAssoc("SELECT * FROM dev", null, "id"));
        $this->assertEquals([["text" => $testText]], $db->fetchAssoc("SELECT text FROM dev"));
        $this->assertEquals([[$testText]], $db->fetchArray("SELECT text FROM dev"));

        // update entry and check if it has been updated
        $this->assertTrue(
            $db->update("dev", ['text' => $testText2], "id = {0} || id = {anyparamname}", [1, "anyparamname" => 1])
        );
        $this->assertEquals([[$testText2]], $db->fetchArray("SELECT text FROM dev"));

        // delete the entry and check if it has been deleted
        $this->assertTrue(
            $db->delete("dev", "id = 1")
        );
        $this->assertEquals([], $db->fetchArray("SELECT text FROM dev"));
        $this->assertNull($db->fetchAssocOne("SELECT text FROM dev"));
        $this->assertNull($db->fetchOne("SELECT text FROM dev"));

        // re-insert some entries for later tests
        $db->insert("dev", ['text' => $testText]);
        $db->insert("dev", ['text' => $testText]);
        $db->insert("dev", ['text' => $testText]);
        $db->insert("dev", ['text' => $testText]);
        $db->insert("dev", ['text' => $testText]);
        $db->insert("dev", ['text' => null]);
        $db->insert("dev", ['text' => [$testText]]);
        $db->insert("dev", ['text' => 7.6]);
        $db->insert("dev", ['text' => DateTime::create('now')]);
        $db->insert("dev", ['text' => new ReflectionClass(__CLASS__)]);
        $db->insert(TestStorable2::class, ['id' => 6666], "REPLACE");
        $this->assertCount(2, $db->fetchArray("SELECT text FROM dev", null, 2));
    }

    /**
     * @depends testQueries
     */
    public function testExceptionDbQuery()
    {
        $db = Mysql::get('test');
        Config::set('devMode', true);
        $e = null;
        try {
            // enable dev mode for this test
            $db->queryRaw('foo');
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::MYSQL_QUERY_ERROR, $e);
        $e = null;
        try {
            $db->queryRaw('DESCRIBE 1');
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::MYSQL_QUERY_ERROR, $e);
    }

    /**
     * @depends testExceptionDbQuery
     */
    public function testExceptionNotExistingFetchIndex()
    {
        $e = null;
        try {
            $db = Mysql::get('test');
            $db->fetchAssoc('SELECT text FROM dev', null, 'foo');
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::MYSQL_FETCH_ASSOC_INDEX_MISSING, $e);
    }

    /**
     * @depends testExceptionNotExistingFetchIndex
     */
    public function testExceptionUnsupportedDbValue()
    {
        $e = null;
        try {
            $db = Mysql::get('test');
            // a resource is an unsupported db value
            $db->insert("dev", ['text' => fopen(__FILE__, 'r')]);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::MYSQL_UNSUPPORTED_DB_PARAMETER, $e);
    }

    /**
     * Drop dev tables after execution
     * @depends testExceptionUnsupportedDbValue
     */
    public function testCleanup(): void
    {
        $this->expectNotToPerformAssertions();
        Mysql::get('test')->query("DROP TABLE IF EXISTS `dev`");
    }
}