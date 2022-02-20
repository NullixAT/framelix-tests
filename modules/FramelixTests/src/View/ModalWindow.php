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
        <button class="framelix-button" data-action="alert">Open Alert</button>
        <div class="framelix-spacer"></div>
        <button class="framelix-button" data-action="confirm">Open Confirm</button>
        <div class="framelix-spacer"></div>
        <button class="framelix-button" data-action="prompt">Open Prompt</button>
        <div class="framelix-spacer"></div>
        <script>
          (function () {
            $(document).on('click', '.framelix-button[data-action]', async function () {
              let modal
              switch (this.dataset.action) {
                case 'alert':
                  modal = FramelixModal.alert('Alert Test')
                  break
                case 'confirm':
                  modal = FramelixModal.confirm('Confirm Test')
                  break
                case 'prompt':
                  modal = FramelixModal.prompt('Prompt Test')
                  FramelixToast.success(await modal.promptResult)
                  break
              }
            })
          })()
        </script>
        <?php
    }
}