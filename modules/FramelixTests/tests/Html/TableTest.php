<?php

namespace Html;

use Framelix\Framelix\Html\Table;
use Framelix\FramelixTests\TestCase;

final class TableTest extends TestCase
{

    public function tests(): void
    {
        $object = new Table();
        $this->callMethodsGeneric($object);
    }
}
