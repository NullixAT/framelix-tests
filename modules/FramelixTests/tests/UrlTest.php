<?php

use Framelix\Framelix\Config;
use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Url;
use Framelix\FramelixTests\TestCase;

final class UrlTest extends TestCase
{
    public function tests(): void
    {
        Config::set('applicationHost', 'localhost');
        Config::set('urlGlobalContextParameterKeys', ['car']);
        Config::set('languageMultiple', true);
        Config::set('languagesSupported', ['de', 'en']);

        // tests with global vars and generic stuff
        $indexPhp = __DIR__ . "/../public/index.php";
        touch($indexPhp);
        $fakeUrlStr = 'http://localhost/foobar?param=zar&car=nar';
        $_GET['car'] = "nar";
        $fakeUrl = Url::create($fakeUrlStr);
        $this->setSimulatedUrl($fakeUrl);
        $this->assertTrue($fakeUrl->hasParameterWithValue('zar'));
        $this->assertFalse($fakeUrl->hasParameterWithValue(''));
        $this->assertSame($fakeUrlStr, Url::create()->jsonSerialize());
        $this->assertSame($fakeUrlStr, Url::create($fakeUrl)->jsonSerialize());
        $this->assertSame($fakeUrlStr, Url::getBrowserUrl()->jsonSerialize());
        $fakeUrl->removeGlobalContextParameters();
        Config::set('urlGlobalContextParameterKeys', null);

        // test getUrlToFile
        $this->assertSame(
            null,
            Url::getUrlToFile("notexist")
        );
        $this->assertSame(
            null,
            Url::getUrlToFile('')
        );
        $this->assertSame(
            'http://localhost/index.php',
            (string)Url::getUrlToFile($indexPhp, FRAMELIX_MODULE, false)
        );
        $this->assertSame(
            'http://localhost/@FramelixTests/index.php',
            (string)Url::getUrlToFile($indexPhp, "Framelix", false)
        );
        $this->assertStringStartsWith(
            'http://localhost/@FramelixTests/index.php?t=',
            (string)Url::getUrlToFile($indexPhp, "Framelix", true)
        );
        // test getModulePublicFolderUrl
        $this->assertSame(
            'http://localhost/',
            (string)Url::getModulePublicFolderUrl("Framelix")
        );

        // test remove parameter
        $fakeUrl = Url::create($fakeUrlStr);
        $fakeUrl->removeParameterByValue('zar');
        $fakeUrl->removeParameterByValue('');
        $this->assertSame(
            'http://localhost/foobar?car=nar',
            (string)$fakeUrl
        );

        // test url updating
        $fakeUrlStr = 'http://user:pass@localhost:4430/foobar?param=zar&car=nar#hash';
        $this->setSimulatedUrl($fakeUrlStr);
        $url = Url::create();
        $this->assertSame($fakeUrlStr, (string)$url);
        $url->update("http://test/balance?nothing");
        $this->assertSame("http://user:pass@test:4430/balance?param=zar&car=nar&nothing=#hash", (string)$url);
        $url->update($fakeUrlStr, true);
        $this->assertSame($fakeUrlStr, (string)$url);

        // test getter/setter
        $this->assertSame(4430, $url->getPort());
        $url->setPort(222);
        $this->assertSame(222, $url->getPort());

        $this->assertSame('http', $url->getScheme());
        $url->setScheme('bla');
        $this->assertSame('bla', $url->getScheme());

        $this->assertSame('localhost', $url->getHost());
        $url->setHost('bla');
        $this->assertSame('bla', $url->getHost());

        $this->assertSame('user', $url->getUsername());
        $url->setUsername('bla');
        $this->assertSame('bla', $url->getUsername());

        $this->assertSame('pass', $url->getPassword());
        $url->setPassword('bla');
        $this->assertSame('bla', $url->getPassword());

        $this->assertSame('hash', $url->getHash());
        $url->setHash('123');
        $this->assertSame('123', $url->getHash());
        $url->setHash(null);
        $this->assertSame(null, $url->getHash());

        $url = Url::create('http://localhost');
        $url->setParameter('foo', ['bar' => 'war']);
        $this->assertSame('http://localhost?foo%5Bbar%5D=war', (string)$url);
        $this->assertSame(["foo[bar]" => 'war'], $url->getParameters());

        // test language in url
        $fakeUrlStr = 'http://localhost/en/bla';
        $this->setSimulatedUrl($fakeUrlStr);
        $url = Url::create();
        $this->assertFalse($url->hasParameterWithValue(''));
        $this->assertSame('en', $url->getLanguage());
        $url->replaceLanguage("de");
        $this->assertSame('de', $url->getLanguage());
        $this->assertSame('http://localhost/de/bla', (string)$url);

        // test sign/verify
        $url->sign();
        $this->assertNotNull($url->getParameter('__s'));
        $this->assertNotNull($url->getParameter('__expires'));
        $this->assertTrue($url->verify());

        $e = null;
        $s = $url->getParameter('__s');
        try {
            $url->setParameter('__s', $s . "1");
            $url->verify();
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::URL_INCORRECT_SIGNATURE, $e);
        $url->setParameter('__s', $s);

        $e = null;
        $url = Url::create();
        $url->sign(true, 0);
        try {
            $url->verify();
        } catch (Throwable $e) {
        }

        $e = null;
        try {
            $url->removeParameter("__s");
            $url->verify();
        } catch (Throwable $e) {
        }
        $this->assertFalse($url->verify(false));
        $this->assertFramelixErrorCode(ErrorCode::URL_MISSING_SIGNATURE, $e);
    }
}
