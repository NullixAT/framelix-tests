<?php

namespace Utils;

use Framelix\Framelix\Config;
use Framelix\Framelix\Utils\Shell;
use Framelix\FramelixUnitTests\TestCase;

use function escapeshellarg;

final class ShellTest extends TestCase
{

    public function tests(): void
    {
        Config::set('shellAliases[blub]', 'aPathToSomeProgramm');
        $this->assertSame('2>&1 php -v', Shell::prepare('php -v')->cmd);
        // use parameters to concat in a row at the end
        $this->assertSame(
            '2>&1 php -v ' . escapeshellarg('some') . " " . escapeshellarg('more') . " " . escapeshellarg('params'),
            Shell::prepare('php -v {*}', ['some', 'more', 'params'])->cmd
        );
        // use specific parameters
        $this->assertSame(
            '2>&1 php -v ' . escapeshellarg('some') . " " . escapeshellarg('params') . " " . escapeshellarg('more'),
            Shell::prepare('php -v {0} {2} {1}', ['some', 'more', 'params'])->cmd
        );
        // using an alias
        $this->assertSame('2>&1 aPathToSomeProgramm -v', Shell::prepare('blub -v')->cmd);
        // testing execution
        $this->assertSame(['123'], Shell::prepare('echo 123')->execute()->output);
    }
}
