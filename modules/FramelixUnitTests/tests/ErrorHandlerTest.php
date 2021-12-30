<?php

use Framelix\Framelix\Config;
use Framelix\Framelix\ErrorHandler;
use Framelix\Framelix\Utils\Buffer;
use Framelix\Framelix\Utils\FileUtils;
use Framelix\FramelixUnitTests\TestCase;

final class ErrorHandlerTest extends TestCase
{
    public function tests(): void
    {
        Config::set('devMode', false);
        try {
            Config::set('errorLogExtended', true);
            throw new \Framelix\Framelix\Exception("test");
        } catch (Throwable $e) {
            $this->assertIsArray(ErrorHandler::throwableToJson($e));
        }
        $e = null;
        try {
            Config::set('errorLogExtended', false);
            throw new \Framelix\Framelix\Exception("test");
        } catch (Throwable $e) {
            $this->assertIsArray(ErrorHandler::throwableToJson($e));
            Buffer::start();
            ErrorHandler::showErrorFromExceptionLog(
                ErrorHandler::throwableToJson($e),
                false
            );
            $this->assertTrue(str_contains(Buffer::get(), '<pre'));
            Buffer::start();
            ErrorHandler::showErrorFromExceptionLog(
                ErrorHandler::throwableToJson($e),
                true
            );
            $this->assertTrue(str_contains(Buffer::get(), '<pre'));
        }
        $logDir = FRAMELIX_APP_ROOT . "/logs";
        $logFiles = FileUtils::getFiles($logDir, "~\.php$~");
        foreach ($logFiles as $logFile) {
            unlink($logFile);
        }
        Config::set('errorLogDisk', true);
        ErrorHandler::saveErrorLogToDisk(ErrorHandler::throwableToJson($e));
        $logFiles = FileUtils::getFiles($logDir, "~\.php$~");
        $this->assertCount(1, $logFiles);
        foreach ($logFiles as $logFile) {
            unlink($logFile);
        }
    }
}
