<?php

namespace Html;

use Framelix\Framelix\Html\Tabs;
use Framelix\FramelixTests\TestCase;

final class TabsTest extends TestCase
{

    public function tests(): void
    {
        $object = new Tabs();
        $this->callMethodsGeneric($object);
    }
}
