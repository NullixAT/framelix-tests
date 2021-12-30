<?php

namespace Utils;

use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Url;
use Framelix\Framelix\Utils\HtmlUtils;
use Framelix\FramelixUnitTests\TestCase;
use Throwable;

final class HtmlUtilsTest extends TestCase
{

    public function tests(): void
    {
        $this->assertSame('&amp;', HtmlUtils::escape('&'));
        $this->assertSame("&amp;<br />\n", HtmlUtils::escape("&\n", true));
        $this->assertIsString(HtmlUtils::getIncludeTagForUrl(Url::create()->appendPath(".css")));
        $this->assertIsString(HtmlUtils::getIncludeTagForUrl(Url::create()->appendPath(".js")));
        $e = null;
        try {
            HtmlUtils::getIncludeTagForUrl(Url::create()->appendPath(".jpeg"));
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::HTMLUTILS_INCLUDE_INVALID_EXTENSION, $e);
    }
}
