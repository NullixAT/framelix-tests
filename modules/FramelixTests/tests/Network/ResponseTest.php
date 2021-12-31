<?php

namespace Network;

use Framelix\Framelix\Network\Response;
use Framelix\Framelix\StopException;
use Framelix\Framelix\Utils\Buffer;
use Framelix\Framelix\Utils\FileUtils;
use Framelix\Framelix\Utils\JsonUtils;
use Framelix\FramelixTests\Storable\TestStorableFile;
use Framelix\FramelixTests\TestCase;

use function file_get_contents;
use function file_put_contents;
use function unlink;

final class ResponseTest extends TestCase
{

    public function tests(): void
    {
        $storableFile = new TestStorableFile();
        FileUtils::deleteDirectory($storableFile->folder);
        mkdir($storableFile->folder);

        Buffer::start();
        try {
            Response::download("@filecontent", "foo");
        } catch (StopException) {
            $this->assertSame("filecontent", Buffer::get());
        }
        Buffer::start();
        try {
            Response::download(__FILE__, "foo", null, function () {
            });
        } catch (StopException) {
            $this->assertSame(file_get_contents(__FILE__), Buffer::get());
        }
        $file = new TestStorableFile();
        $file->relativePathOnDisk = "test.txt";
        $filePath = $file->getPath(false);
        Buffer::start();
        try {
            file_put_contents($filePath, "foobar");
            Response::download($file);
        } catch (StopException) {
            $this->assertSame("foobar", Buffer::get());
        }
        unlink($file->getPath(true));

        // not exist test
        Buffer::start();
        try {
            Response::download(__FILE__ . "NotExist");
        } catch (StopException) {
            $this->assertSame("", Buffer::get());
        }

        // not exist test
        Buffer::start();
        try {
            Response::download($file);
        } catch (StopException) {
            $this->assertSame("", Buffer::get());
        }

        Buffer::start();
        try {
            Response::showFormAsyncSubmitResponse();
        } catch (StopException) {
            $this->assertSame(200, http_response_code());
            $this->assertTrue(true);
            $this->assertSame('{"modalMessage":null,"reloadTab":false,"toastMessages":[]}', Buffer::get());
        }

        Buffer::start();
        try {
            Response::showFormValidationErrorResponse('foobar');
        } catch (StopException) {
            $this->assertSame(406, http_response_code());
            $this->assertSame(JsonUtils::encode("foobar"), Buffer::get());
        }
        FileUtils::deleteDirectory($storableFile->folder);
    }
}
