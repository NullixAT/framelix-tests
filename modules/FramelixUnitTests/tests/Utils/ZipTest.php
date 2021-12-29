<?php

namespace Utils;

use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Utils\FileUtils;
use Framelix\Framelix\Utils\Zip;
use Framelix\FramelixUnitTests\TestCase;
use Throwable;

final class ZipTest extends TestCase
{
    public function tests(): void
    {
        $zipFile = __DIR__ . "/../../tmp/test.zip";
        $zipCreateFile = __DIR__ . "/../../tmp/ziptest/test.zip";
        $tmpFolder = __DIR__ . "/../../tmp/ziptest";
        $packFolder = __DIR__ . "/../../tmp/fileutils-test";
        FileUtils::deleteDirectory($tmpFolder);
        mkdir($tmpFolder);

        $e = null;
        try {
            Zip::unzip($zipFile . "NotExist", $tmpFolder);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::ZIP_UNZIP_NOFILE, $e);

        $e = null;
        try {
            Zip::unzip($zipFile, $tmpFolder . "NotExist");
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::ZIP_UNZIP_NODIRECTORY, $e);

        $e = null;
        try {
            Zip::unzip(__FILE__, $tmpFolder);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::ZIP_OPEN, $e);
        $e = null;
        try {
            Zip::createZip(__DIR__, []);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::ZIP_OPEN, $e);

        Zip::unzip($zipFile, $tmpFolder, true);
        $this->assertFilelist([
            'modules/FramelixUnitTests/tmp/ziptest/fileutils-test/sub/test1',
            'modules/FramelixUnitTests/tmp/ziptest/fileutils-test/sub/test1.txt',
            'modules/FramelixUnitTests/tmp/ziptest/fileutils-test/test1',
            'modules/FramelixUnitTests/tmp/ziptest/fileutils-test/test1.txt'
        ], $tmpFolder);

        $e = null;
        try {
            Zip::unzip($zipFile, $tmpFolder);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::ZIP_UNZIP_NOTEMPTY, $e);

        FileUtils::deleteDirectory($tmpFolder);
        mkdir($tmpFolder);
        $packFiles = [];
        $files = FileUtils::getFiles($packFolder, null, true, true);
        foreach ($files as $file) {
            $packFiles[FileUtils::getRelativePathToBase($file, $packFolder)] = $file;
        }
        Zip::createZip($zipCreateFile, $packFiles);

        Zip::unzip($zipCreateFile, $tmpFolder, true);
        $this->assertFilelist([
            'modules/FramelixUnitTests/tmp/ziptest/.gitignore',
            'modules/FramelixUnitTests/tmp/ziptest/sub/test1',
            'modules/FramelixUnitTests/tmp/ziptest/sub/test1.txt',
            'modules/FramelixUnitTests/tmp/ziptest/test.zip',
            'modules/FramelixUnitTests/tmp/ziptest/test1',
            'modules/FramelixUnitTests/tmp/ziptest/test1.txt'
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
