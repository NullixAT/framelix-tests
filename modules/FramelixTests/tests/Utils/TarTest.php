<?php

namespace Utils;

use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Utils\FileUtils;
use Framelix\Framelix\Utils\Tar;
use Framelix\Framelix\Utils\Zip;
use Framelix\FramelixTests\TestCase;
use Phar;
use Throwable;

final class TarTest extends TestCase
{
    public function tests(): void
    {
        $tarFile = __DIR__ . "/../../tmp/test.tar";
        $tarCreateFile = __DIR__ . "/../../tmp/tartest/test.zip";
        $tmpFolder = __DIR__ . "/../../tmp/tartest";
        $packFolder = __DIR__ . "/../../tmp/fileutils-test";
        FileUtils::deleteDirectory($tmpFolder);
        mkdir($tmpFolder);

        $e = null;
        try {
            Tar::extractTo($tarFile . "NotExist", $tmpFolder);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::TAR_EXTRACT_NOFILE, $e);

        $e = null;
        try {
            Tar::extractTo($tarFile, $tmpFolder . "NotExist");
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::TAR_EXTRACT_NODIRECTORY, $e);

        $e = null;
        try {
            Tar::extractTo(__FILE__, $tmpFolder);
        } catch (Throwable $e) {
        }
        $this->assertInstanceOf(Throwable::class, $e);
        $e = null;
        try {
            Tar::extractTo(__DIR__, $tmpFolder);
        } catch (Throwable $e) {
        }
        $this->assertInstanceOf(Throwable::class, $e);

        Tar::extractTo($tarFile, $tmpFolder, true);
        $this->assertFilelist([
            'modules/FramelixTests/tmp/tartest/fileutils-test/.gitignore',
            'modules/FramelixTests/tmp/tartest/fileutils-test/sub/test1',
            'modules/FramelixTests/tmp/tartest/fileutils-test/sub/test1.txt',
            'modules/FramelixTests/tmp/tartest/fileutils-test/test1',
            'modules/FramelixTests/tmp/tartest/fileutils-test/test1.txt'
        ], $tmpFolder);

        $e = null;
        try {
            Tar::extractTo($tarFile, $tmpFolder);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::TAR_EXTRACT_NOTEMPTY, $e);

        FileUtils::deleteDirectory($tmpFolder);
        mkdir($tmpFolder);
        $packFiles = [];
        $files = FileUtils::getFiles($packFolder, null, true, true);
        foreach ($files as $file) {
            $packFiles[FileUtils::getRelativePathToBase($file, $packFolder)] = $file;
        }
        Tar::createTar($tarCreateFile, $packFiles);

        Tar::extractTo($tarCreateFile, $tmpFolder, true);
        $this->assertFilelist([
            'modules/FramelixTests/tmp/tartest/.gitignore',
            'modules/FramelixTests/tmp/tartest/sub/test1',
            'modules/FramelixTests/tmp/tartest/sub/test1.txt',
            'modules/FramelixTests/tmp/tartest/test.zip',
            'modules/FramelixTests/tmp/tartest/test1',
            'modules/FramelixTests/tmp/tartest/test1.txt'
        ], $tmpFolder);
        FileUtils::deleteDirectory($tmpFolder);
    }

    /**
     * Assert a filelist to match exactly in given folder
     * @param string[] $expected
     * @param string $folder
     * @return void
     */
    private function assertFilelist(array $expected, string $folder): void
    {
        $actual = FileUtils::getFiles($folder, null, true);
        foreach ($actual as $key => $value) {
            $actual[$key] = FileUtils::getRelativePathToBase($value);
        }
        $this->assertSame($expected, $actual);
    }
}
