<?php

use Framelix\Framelix\Config;
use Framelix\Framelix\ErrorCode;
use Framelix\FramelixTests\TestCase;

final class ConfigTest extends TestCase
{
    public function tests(): void
    {
        Config::$loadedModules = [];
        Config::set('modules', ["Framelix", FRAMELIX_MODULE]);
        Config::load();
        Config::set('devMode', false);
        Config::set('float', 1.0);
        Config::set('bool', true);
        Config::set('int', 1);
        Config::set('string', '1');
        Config::merge(['merged' => ['foo' => 'bar']]);
        $this->assertSame(1.0, Config::get('float', 'float'));
        $this->assertSame(1, Config::get('int', 'int'));
        $this->assertSame(true, Config::get('bool', 'bool'));
        $this->assertSame('1', Config::get('string', 'string'));
        $this->assertSame('bar', Config::get('merged[foo]', 'string'));
        $this->assertIsArray(Config::get('database', 'array'));
        $this->assertFalse(Config::isDevMode());
        $this->assertTrue(Config::keyExists('database'));
        $tmpConfigFile = __DIR__ . "/../config/config-test.php";
        Config::writeConfigToFile(FRAMELIX_MODULE, 'config-test.php', ['foo' => 'data']);
        $this->assertFileExists($tmpConfigFile);
        $configData = file_get_contents($tmpConfigFile);
        unlink($tmpConfigFile);
        $this->assertTrue(str_contains($configData, '"foo": "data"'));
        $e = null;
        try {
            Config::get('database', 'string');
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::CONFIG_VALUE_INVALID_TYPE, $e);
        $e = null;
        try {
            Config::get('notexistkey', 'string');
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::CONFIG_VALUE_INVALID_TYPE, $e);
    }
}
