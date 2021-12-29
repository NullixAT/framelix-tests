<?php

namespace Utils;

use Framelix\Framelix\Utils\Buffer;
use PHPUnit\Framework\TestCase;

use function ob_start;

final class BufferTest extends TestCase
{

    public function tests(): void
    {
        Buffer::start();
        echo 123;
        Buffer::clear();
        $this->assertSame('', Buffer::get());

        Buffer::start();
        echo 123;
        Buffer::start();
        echo 123;
        $this->assertSame('123123', Buffer::getAll());

        Buffer::start();
        echo "Flushme";
        Buffer::start();
        Buffer::flush();
        $this->assertSame('', Buffer::getAll());

        Buffer::start();
        echo 123;
        $this->assertSame('123', Buffer::get());

        // empty buffer
        $this->assertSame('', Buffer::get());
        // create buffer for phpunit later on
        ob_start();
    }
}
