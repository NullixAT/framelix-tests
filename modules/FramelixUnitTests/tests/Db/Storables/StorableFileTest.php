<?php

namespace Db\Storables;

use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Network\UploadedFile;
use Framelix\Framelix\Url;
use Framelix\Framelix\Utils\FileUtils;
use Framelix\FramelixUnitTests\Storable\TestStorableFile;
use Framelix\FramelixUnitTests\TestCase;
use Throwable;

use function mkdir;

final class StorableFileTest extends TestCase
{
    public function test(): void
    {
        $storableFile = new TestStorableFile();
        FileUtils::deleteDirectory($storableFile->folder);
        mkdir($storableFile->folder);

        $this->setupDatabase();
        $this->addSimulatedFile("test.txt", "foobar", false);

        // simulate not existing file
        $e = null;
        try {
            $uploadedFile = UploadedFile::createFromSubmitData("test.txt")[0];
            $uploadedFile->path .= "1";
            $storableFile = new TestStorableFile();
            $storableFile->store($uploadedFile);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::STORABLEFILE_FILE_NOTEXIST, $e);

        // simulate not existing folder
        $e = null;
        try {
            $uploadedFile = UploadedFile::createFromSubmitData("test.txt")[0];
            $storableFile = new TestStorableFile();
            $storableFile->folder .= "YXZ";
            $storableFile->store($uploadedFile);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::STORABLEFILE_FOLDER_NOTEXIST, $e);

        // simulate missing file
        $e = null;
        try {
            $storableFile = new TestStorableFile();
            $storableFile->store();
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::STORABLEFILE_FILE_MISSING, $e);

        // simulate missing filename
        $e = null;
        try {
            $storableFile = new TestStorableFile();
            $storableFile->store("foobar");
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::STORABLEFILE_FILENAME_MISSING, $e);

        $storableFile = new TestStorableFile();
        $storableFile->filename = "test.txt";
        $storableFile->store("test");

        $storableFile2 = new TestStorableFile();
        $storableFile2->filename = "test.txt";
        $storableFile2->store("test");
        $this->assertInstanceOf(Url::class, $storableFile->getDownloadUrl());
        $this->assertIsString($storableFile->getHtmlString());
        $this->assertSame('test', $storableFile->getFiledata());
        $this->assertSame('test', $storableFile2->getFiledata());
        $storableFile->delete();
        $storableFile2->delete();
        $this->assertNull($storableFile2->getDownloadUrl());
        // deleted file return html string anyway
        $this->assertIsString($storableFile->getHtmlString());

        $this->addSimulatedFile("test.txt", "foobar", false);
        $uploadedFile = UploadedFile::createFromSubmitData("test.txt")[0];
        $storableFile = new TestStorableFile();
        $storableFile->store($uploadedFile);

        // restore to test update functionality
        $this->addSimulatedFile("test.txt", "foobar", false);
        $uploadedFile = UploadedFile::createFromSubmitData("test.txt")[0];
        $storableFile->store($uploadedFile);
        $storableFile->store("foobar2");
        $this->assertSame('foobar2', $storableFile->getFiledata());

        // only update metadata without file changes
        $storableFile->filename = "foo";
        $storableFile->store();

        $storableFile->delete();

        $this->assertNull($storableFile->getFiledata());

        FileUtils::deleteDirectory($storableFile->folder);
    }
}