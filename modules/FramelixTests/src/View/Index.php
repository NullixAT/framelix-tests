<?php

namespace Framelix\FramelixTests\View;

use Framelix\Framelix\View;

/**
 * Index
 */
class Index extends View
{

    /**
     * Access role
     * @var string|bool
     */
    protected string|bool $accessRole = "*";

    /**
     * On request
     */
    public function onRequest(): void
    {
    }
}