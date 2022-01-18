<?php

namespace Utils;

use Framelix\Framelix\Utils\Browser;
use PHPUnit\Framework\TestCase;

use function fsockopen;

final class BrowserTest extends TestCase
{

    public function tests(): void
    {
        // check first if we have a network connection
        if (!fsockopen('1.1.1.1', port: 443, timeout: 3)) {
            $this->expectNotToPerformAssertions();
            return;
        }
        $browser = Browser::create();
        $browser->url = 'https://1.1.1.1';
        $browser->validateSsl = false;
        $this->assertSame(0, $browser->getResponseCode());
        $browser->sendRequest();
        $this->assertSame(200, $browser->getResponseCode());
    }
}
