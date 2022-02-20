<?php

namespace Framelix\FramelixTests\View;

use Framelix\Framelix\View\Backend\View;

/**
 * ModalWindow
 */
class ModalWindow extends View
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
        $this->showContentBasedOnRequestType();
    }

    /**
     * Show content
     */
    public function showContent(): void
    {
        ?>
        <button class="framelix-button">Open Modal</button>
        <?php
    }
}