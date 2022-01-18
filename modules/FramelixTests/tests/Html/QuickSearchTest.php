<?php

namespace Html;

use Framelix\Framelix\Html\QuickSearch;
use Framelix\FramelixTests\TestCase;

final class QuickSearchTest extends TestCase
{

    public function tests(): void
    {
        $quickSearch = new QuickSearch();
        $this->callMethodsGeneric($quickSearch, ['addOptionField', 'addOptionFields', 'addOptionsFields']);
    }
}
