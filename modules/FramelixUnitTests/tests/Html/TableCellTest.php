<?php

namespace Html;

use Framelix\Framelix\Html\TableCell;
use Framelix\FramelixUnitTests\TestCase;

final class TableCellTest extends TestCase
{

    public function tests(): void
    {
        $object = new TableCell();
        $this->callMethodsGeneric($object);
    }
}
