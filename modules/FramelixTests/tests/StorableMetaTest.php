<?php

use Framelix\Framelix\Db\LazySearchCondition;
use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Form\Form;
use Framelix\Framelix\Html\QuickSearch;
use Framelix\Framelix\Html\Table;
use Framelix\Framelix\Network\JsCall;
use Framelix\Framelix\StorableMeta;
use Framelix\Framelix\Url;
use Framelix\Framelix\Utils\Buffer;
use Framelix\Framelix\Utils\JsonUtils;
use Framelix\FramelixUnitTests\Storable\TestStorableSystemValue;
use Framelix\FramelixUnitTests\StorableMeta\TestStorable2;
use Framelix\FramelixUnitTests\TestCase;

final class StorableMetaTest extends TestCase
{
    public function tests(): void
    {
        $this->setupDatabase(true);
        $this->setSimulatedUrl('http://localhost');
        $storable = new \Framelix\FramelixUnitTests\Storable\TestStorable2();
        $meta = new TestStorable2($storable);
        $meta->lazySearchConditionDefault->addColumn('longText', 'longText', 'string');
        $this->callMethodsGeneric(
            $meta,
            ['createFromUrl', 'getTable', 'getTableWithStorableSorting', 'showSearchAndTableInTabs']
        );
        $this->assertInstanceOf(QuickSearch::class, $meta->getQuickSearch());
        $this->assertInstanceOf(
            LazySearchCondition::class,
            $meta->getQuickSearchCondition(['customOption' => 1])
        );
        $this->assertInstanceOf(
            Table::class,
            $meta->getTable([$storable])
        );
        $this->assertInstanceOf(
            Table::class,
            $meta->getTableWithStorableSorting([$storable])
        );
        $this->assertInstanceOf(
            Form::class,
            $meta->getEditForm()
        );
        $e = null;
        try {
            $meta->getTable(["foo"]);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::STORABLEMETA_NOSTORABLE, $e);
        // simulate user has opened an edit url in browser, result in 2 buttons
        $storable->id = 1;
        $this->setSimulatedHeader('http_x_browser_url', "http://localhost?param=" . $storable->id);
        $this->assertCount(2, $meta->getEditForm()->buttons);
        $storable->id = null;

        // test table and tabs combination
        Buffer::start();
        $meta->showSearchAndTableInTabs([$storable]);
        $this->assertTrue(str_contains(Buffer::get(), 'Tabs'));
        $this->assertSame(
            JsonUtils::encode($meta),
            JsonUtils::encode(
                StorableMeta::createFromUrl(Url::create()->addParameters($meta->jsonSerialize()))
            )
        );

        // testing jscalls
        $params = [];
        $systemValueTest = new TestStorableSystemValue();
        $systemValueTest->name = '1';
        $systemValueTest->sort = 1;
        $systemValueTest->flagActive = true;
        $systemValueTest->store();
        $params[] = ['id' => $systemValueTest->id, 'connection-id' => $systemValueTest->connectionId];
        $systemValueTest = new TestStorableSystemValue();
        $systemValueTest->name = '2';
        $systemValueTest->sort = 2;
        $systemValueTest->flagActive = true;
        $systemValueTest->store();
        $params[] = ['id' => $systemValueTest->id, 'connection-id' => $systemValueTest->connectionId];
        $jsCall = new JsCall('savesort', ['ids' => $params]);
        TestStorable2::onJsCall($jsCall);
        $this->assertTrue($jsCall->result);

        $this->setSimulatedGetData($meta->jsonSerialize());
        $jsCall = new JsCall('quicksearch', ['query' => '']);
        TestStorable2::onJsCall($jsCall);
        $this->assertTrue($jsCall->result === '');

        Buffer::start();
        $jsCall = new JsCall('quicksearch', ['query' => 'test']);
        TestStorable2::onJsCall($jsCall);
        $this->assertTrue(str_contains(Buffer::get(), "<div"));
    }
}
