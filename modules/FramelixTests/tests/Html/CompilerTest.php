<?php

namespace Html;

use Framelix\Framelix\Config;
use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Html\Compiler;
use Framelix\Framelix\Url;
use Framelix\Framelix\Utils\FileUtils;
use Framelix\FramelixTests\TestCase;
use Throwable;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function rename;
use function time;
use function touch;
use function unlink;

use const FRAMELIX_MODULE;

final class CompilerTest extends TestCase
{

    public function testCleanup(): void
    {
        // remove previous dist files
        $files = FileUtils::getFiles(__DIR__ . "/../../public/dist", "~\.(css|js|json)$~i", true);
        foreach ($files as $file) {
            unlink($file);
        }
        $files = FileUtils::getFiles(__DIR__ . "/../../public/dist", "~\.(css|js|json)$~i", true);
        $this->assertCount(0, $files);
    }

    /**
     * @depends testCleanup
     */
    public function testBabel(): void
    {
        $babelFolder = __DIR__ . "/../../../Framelix/node_modules/@babel";
        // move folder if exist to test exception
        if (is_dir($babelFolder)) {
            rename($babelFolder, $babelFolder . "_tmp");
        }
        Config::set('devMode', false);
        $this->assertFalse(Compiler::isCompilerAvailable());
        // when not in dev mode, calling a compiler if if not available does nothing
        $this->assertNull(Compiler::compile(FRAMELIX_MODULE));
        // in dev mode, compiling is active
        Config::set('devMode', true);
        $e = null;
        try {
            Compiler::compile(FRAMELIX_MODULE);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::COMPILER_BABEL_MISSING, $e);

        // move folder back
        if (is_dir($babelFolder . "_tmp")) {
            rename($babelFolder . "_tmp", $babelFolder);
        }
        $this->assertTrue(Compiler::isCompilerAvailable());

        // testing compiler exception when something goes wrong with script execution
        $compilerJs = __DIR__ . "/../../../Framelix/nodejs/compiler.js";
        rename($compilerJs, $compilerJs . "-tmp");
        $e = null;
        try {
            Compiler::$cache = [];
            Compiler::compile(FRAMELIX_MODULE);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::COMPILER_COMPILE_ERROR, $e);
        rename($compilerJs . "-tmp", $compilerJs);
    }

    /**
     * @depends testBabel
     */
    public function tests(): void
    {
        $distFolder = __DIR__ . "/../../public/dist";
        $noCompiledFile = $distFolder . "/js/test-nocompile.js";
        $metaFile = __DIR__ . "/../../public/dist/_meta.json";
        Config::set('devMode', true);
        $this->assertTrue(!file_exists($metaFile));
        $this->assertCount(8, Compiler::compile(FRAMELIX_MODULE));
        // already cached
        $this->assertNull(Compiler::compile(FRAMELIX_MODULE));
        // reset cache, should still not update but do filechecks
        Compiler::$cache = [];
        $this->assertCount(0, Compiler::compile(FRAMELIX_MODULE));
        Compiler::$cache = [];
        // updating a file timestamp to newer time will trigger recompile
        // of each group where this file is in
        $jsFile = __DIR__ . "/../../js/framelix-unit-test-jstest.js";
        touch($jsFile, time() + 1);
        $this->assertCount(5, Compiler::compile(FRAMELIX_MODULE));
        // compiling invalid module does nothing
        $this->assertNull(Compiler::compile("FOO"));
        $this->assertFileExists($metaFile);
        // injecting a dist file that no exist in config, will trigger delete of this file
        Compiler::$cache = [];
        file_put_contents($distFolder . "/css/fakefile.css", '');
        $distFiles = FileUtils::getFiles($distFolder, null, true);
        Compiler::compile(FRAMELIX_MODULE);
        $this->assertCount(count($distFiles) - 1, FileUtils::getFiles($distFolder, null, true));

        $this->assertCount(6, Compiler::getDistMetadata(FRAMELIX_MODULE)["js"]);
        $this->assertCount(3, Compiler::getDistMetadata(FRAMELIX_MODULE)["scss"]);

        $this->assertStringEndsWith(
            trim(file_get_contents(__DIR__ . "/../../js/framelix-unit-test-jstest.js")),
            trim(file_get_contents($noCompiledFile)),
        );
    }

    /**
     * @depends tests
     */
    public function testUrls(): void
    {
        // testing get metadata when it is not already in cache
        $this->assertCount(6, Compiler::getDistMetadata(FRAMELIX_MODULE)["js"]);
        $this->assertCount(3, Compiler::getDistMetadata(FRAMELIX_MODULE)["scss"]);

        $this->assertInstanceOf(Url::class, Compiler::getDistUrl(FRAMELIX_MODULE, "js", "test-path"));
        // error when trying to get url fo non existing compiled file
        $e = null;
        try {
            Compiler::getDistUrl(FRAMELIX_MODULE, "js", "test-paths");
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::COMPILER_DISTFILE_NOTEXIST, $e);
        // cleanup all dist files
        $this->testCleanup();
    }
}
