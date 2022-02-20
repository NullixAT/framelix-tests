<?php

use Framelix\Framelix\Config;
use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\ErrorHandler;
use Framelix\Framelix\Lang;
use Framelix\Framelix\Url;
use Framelix\Framelix\Utils\Buffer;
use Framelix\Framelix\View;
use Framelix\FramelixTests\TestCase;
use Framelix\FramelixTests\View\Index;
use Framelix\FramelixTests\View\TestView;
use Framelix\FramelixTests\View\TestViewCustomUrl;
use Framelix\FramelixTests\View\TestViewRegexUrl;

final class ViewTest extends TestCase
{
    public function tests(): void
    {
        Config::set('applicationHttps', false);
        Config::set('applicationHost', 'localhost');
        Config::set('applicationUrlBasePath', '');
        Config::set('languageDefault', 'en');
        Config::set('languageFallback', 'en');
        Lang::$lang = "en";

        // test getTranslatedPageTitle
        $e = null;
        try {
            TestView::getTranslatedPageTitle(TestView::class, false);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::TOPLEVEL_CALL_ONLY, $e);
        Lang::set('__framelixtests_view_testview__', '<b>PageTitle</b>');
        $this->assertSame(
            '<b>PageTitle</b>',
            View::getTranslatedPageTitle(TestView::class, false)
        );
        $this->assertSame(
            'PageTitle',
            View::getTranslatedPageTitle(TestView::class, true)
        );

        // test getUrl
        $e = null;
        try {
            TestView::getUrl(TestView::class);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::TOPLEVEL_CALL_ONLY, $e);

        $this->assertNull(View::getUrl("notexist"));
        // notice that index views links to directory name instead
        $this->assertSame('http://localhost/', (string)View::getUrl(Index::class));
        $this->assertSame('http://localhost/testview', (string)View::getUrl(TestView::class));
        $this->assertSame('http://localhost/custom', (string)View::getUrl(TestViewCustomUrl::class));
        // notice missing regex param result in weird url
        $this->assertSame('http://localhost/regex/id0-9+', (string)View::getUrl(TestViewRegexUrl::class));
        $this->assertSame('http://localhost/regex/12', (string)View::getUrl(TestViewRegexUrl::class, ['id' => 12]));
        // test url multilanguage
        Config::set('languageMultiple', true);
        $this->assertSame('http://localhost/en/regex/12', (string)View::getUrl(TestViewRegexUrl::class, ['id' => 12]));

        // test findViewForUrl
        $this->assertInstanceOf(
            TestView::class,
            View::findViewForUrl(Url::create('http://localhost/testview'))
        );
        $this->assertInstanceOf(
            TestViewCustomUrl::class,
            View::findViewForUrl(Url::create('http://localhost/custom'))
        );
        $this->assertInstanceOf(
            TestViewRegexUrl::class,
            View::findViewForUrl(Url::create('http://localhost/regex/12'))
        );
        // unsupported language in url
        $this->assertNull(View::findViewForUrl(Url::create('http://localhost/en/regex/12')));
        // supported language in url
        Config::set('languagesSupported', ['en', 'de']);
        $this->assertInstanceOf(
            TestViewRegexUrl::class,
            View::findViewForUrl(Url::create('http://localhost/en/regex/12'))
        );
        Config::set('languageMultiple', false);

        // misc
        Config::set('devMode', false);
        View::addAvailableViewsByModule("Framelix");
        $this->assertSame(
            'FramelixTests, bar',
            (string)View::replaceAccessRoleParameters('{module}, {foo}', Url::create()->setParameter('foo', 'bar'))
        );

        // update metadata test
        $metadataFile = __DIR__ . "/../_meta/views.json";
        // no dev does nothing
        Config::set('devMode', false);
        View::updateMetadata(FRAMELIX_MODULE);
        Config::set('devMode', true);
        $metadataContents = file_get_contents($metadataFile);
        unlink($metadataFile);
        View::updateMetadata(FRAMELIX_MODULE);
        // calling update again does filetime checks
        // simulate older metadata time
        touch($metadataFile, time() - 8640000);
        View::updateMetadata(FRAMELIX_MODULE);
        $metadataContentsNew = file_get_contents($metadataFile);
        file_put_contents($metadataFile, $metadataContents);
        $this->assertSame($metadataContents, $metadataContentsNew);

        // test get selfUrl
        $view = new TestView();
        $this->assertSame('http://localhost/testview', (string)$view->getSelfUrl());
        $this->assertSame(
            '{"properties":{"url":"http:\/\/localhost\/testview","class":"Framelix\\\\FramelixTests\\\\View\\\\TestView"}}',
            json_encode($view)
        );

        Buffer::start();
        $oldStartIndex = Buffer::$startBufferIndex;
        Buffer::$startBufferIndex = ob_get_level();
        try {
            $view->showAccessDenied();
        } catch (Throwable) {
        }
        Buffer::$startBufferIndex = $oldStartIndex;
        Buffer::clear();
        $this->assertSame(403, http_response_code());
        http_response_code(200);

        Buffer::start();
        $oldStartIndex = Buffer::$startBufferIndex;
        Buffer::$startBufferIndex = ob_get_level();
        try {
            $view->showInvalidUrlError();
        } catch (Throwable) {
        }
        Buffer::$startBufferIndex = $oldStartIndex;
        Buffer::clear();
        $this->assertSame(200, http_response_code());

        Buffer::start();
        $oldStartIndex = Buffer::$startBufferIndex;
        Buffer::$startBufferIndex = ob_get_level();
        try {
            $view->showAccessDenied();
        } catch (Throwable $e) {
            try {
                $view->onException(ErrorHandler::throwableToJson($e));
            } catch (Throwable) {
            }
        }
        Buffer::$startBufferIndex = $oldStartIndex;
        Buffer::clear();
        $this->assertSame(403, http_response_code());
    }
}
