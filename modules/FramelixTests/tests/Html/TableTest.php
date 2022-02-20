<?php

namespace Html;

use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Html\HtmlAttributes;
use Framelix\Framelix\Html\Table;
use Framelix\Framelix\Network\JsCall;
use Framelix\Framelix\Time;
use Framelix\Framelix\Utils\Buffer;
use Framelix\FramelixTests\TestCase;
use Throwable;

final class TableTest extends TestCase
{

    public function tests(): void
    {
        $object = new Table();
        $this->assertInstanceOf(HtmlAttributes::class, $object->getCellHtmlAttributes(1, 'test'));

        $this->callMethodsGeneric($object);

        $jsCall = new JsCall("deleteStorable", null);
        Table::onJsCall($jsCall);

        $table = new Table();
        $table->createHeader(['test' => 'foo']);
        $table->createRow(['test' => 1]);
        $table->createRow(['test' => "1,22"]);
        $table->createRow(['test' => Time::create('01:00')]);
        $table->footerSumColumns = ['test'];
        Buffer::start();
        $table->show();
        Buffer::clear();

        $e = null;
        try {
            $table = new Table();
            $table->footerSumColumns = ['foo'];
            json_encode($table);
        } catch (Throwable $e) {
        }

        $this->assertFramelixErrorCode(ErrorCode::TABLE_FOOTERSUM_COLUMN_NOTEXIST, $e);
    }
}
