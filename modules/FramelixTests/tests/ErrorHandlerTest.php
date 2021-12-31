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
        // extended error log test
        $testException = null;
        try {
            Config::set('errorLogExtended', true);
            throw new \Framelix\Framelix\Exception("test");
        } catch (Throwable $e) {
            $testException = $e;
            $this->assertNotEquals(
                'Available with extended log only',
                ErrorHandler::throwableToJson($testException)['additionalData']['server']
            );
        }
        // normal error log test
        $e = null;
        try {
            Config::set('errorLogExtended', false);
            throw new \Framelix\Framelix\Exception("test");
        } catch (Throwable $e) {
            $this->assertEquals(
                'Available with extended log only',
                ErrorHandler::throwableToJson($testException)['additionalData']['server']
            );
        }
        // testing php error handler
        $e = null;
        try {
            ErrorHandler::onError(E_ERROR, "Test", __FILE__, 20);
        } catch (Throwable $e) {
        }
        $this->assertInstanceOf(Exception::class, $e);
        // testing php error handler with @ suppression
        $e = null;
        $oldReporting = error_reporting();
        try {
            error_reporting(E_ALL & ~E_ERROR);
            ErrorHandler::onError(E_ERROR, "Test", __FILE__, 20);
        } catch (Throwable $e) {
        }
        $this->assertNull($e);
        error_reporting($oldReporting);

        // testing raw show exception log
        Buffer::start();
        ErrorHandler::showErrorFromExceptionLog(
            ErrorHandler::throwableToJson($testException),
            false
        );
        $this->assertTrue(str_contains(Buffer::get(), '<pre'));

        // testing html show exception log
        Buffer::start();
        ErrorHandler::showErrorFromExceptionLog(
            ErrorHandler::throwableToJson($testException),
            true
        );
        $this->assertTrue(str_contains(Buffer::get(), 'phpstorm://open'));

        // testing log to disk
        $logDir = FRAMELIX_APP_ROOT . "/logs";
        $logFiles = FileUtils::getFiles($logDir, "~\.php$~");
        foreach ($logFiles as $logFile) {
            unlink($logFile);
        }
        Config::set('errorLogDisk', true);
        ErrorHandler::saveErrorLogToDisk(ErrorHandler::throwableToJson($testException));
        $logFiles = FileUtils::getFiles($logDir, "~\.php$~");
        $this->assertCount(1, $logFiles);
        foreach ($logFiles as $logFile) {
            unlink($logFile);
        }
    }
}
