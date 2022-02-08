<?php

use Framelix\Framelix\Date;
use Framelix\Framelix\DateTime;
use Framelix\Framelix\Db\LazySearchCondition;
use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Form\Form;
use Framelix\Framelix\Html\QuickSearch;
use Framelix\Framelix\Html\Table;
use Framelix\Framelix\Lang;
use Framelix\Framelix\Network\JsCall;
use Framelix\Framelix\StorableMeta;
use Framelix\Framelix\Time;
use Framelix\Framelix\Url;
use Framelix\Framelix\Utils\Buffer;
use Framelix\Framelix\Utils\JsonUtils;
use Framelix\FramelixTests\Storable\TestStorable1;
use Framelix\FramelixTests\Storable\TestStorableSystemValue;
use Framelix\FramelixTests\StorableMeta\TestStorable2;
use Framelix\FramelixTests\TestCase;

final class StorableMetaTest extends TestCase
{
    public function tests(): void
    {
        // fake lang for coverage
        Lang::$values['en']['__framelixtests_storable_teststorable2_datetime_label_desc__'] = 'foo';

        $this->setupDatabase(true);
        $this->setSimulatedUrl('http://localhost');

        $storable = TestStorable1::getByIdOrNew(1);
        $this->assertNull($storable->id);
        $storable->name = "foobar@dev.me";
        $storable->longText = str_repeat("foo", 100);
        $storable->intNumber = 69;
        $storable->floatNumber = 6.9;
        $storable->boolFlag = true;
        $storable->jsonData = ['foobar', 1];
        $storable->dateTime = DateTime::create('now');
        $storable->date = Date::create('now');
        $storable->store();
        $storableReference = $storable;

        $storable = new \Framelix\FramelixTests\Storable\TestStorable2();
        // modified timestamp is null for new objects
        $this->assertNull($storable->getModifiedTimestampTableCell());
        $storable->name = "foobar@test2.me";
        $storable->longText = str_repeat("foo", 100);
        $storable->longTextLazy = str_repeat("foo", 1000);
        $storable->intNumber = 69;
        $storable->floatNumber = 6.9;
        $storable->boolFlag = true;
        $storable->jsonData = ['foobar', 1];
        $storable->dateTime = new DateTime("2000-01-01 12:23:44");
        $storable->date = Date::create("2000-01-01");
        $storable->otherReferenceOptional = $storableReference;
        $storable->otherReferenceArrayOptional = [$storableReference];
        $storable->typedIntArray = [1, 3, 5];
        $storable->typedBoolArray = [true, false, true];
        $storable->typedStringArray = ["foo" => "yes", "baby", "yes"];
        $storable->typedFloatArray = [1.2, 1.6, 1.7];
        $storable->typedDateArray = [
            DateTime::create("2000-01-01 12:23:44"),
            DateTime::create("2000-01-01 12:23:44 + 10 days"),
            DateTime::create("2000-01-01 12:23:44 + 1 year")
        ];
        $storable->time = Time::create("12:00:01");
        $storable->updateTime = DateTime::create('now - 10 seconds');
        $storable->store();

        $meta = new TestStorable2($storable);
        $this->assertIsArray($meta->jsonSerialize());
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

        $e = null;
        try {
            $params = [];
            $params[] = ['id' => $storableReference->id, 'connection-id' => $storableReference->connectionId];

            $jsCall = new JsCall(
                'savesort',
                ['ids' => $params]
            );
            TestStorable2::onJsCall($jsCall);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::STORABLE_SORT_CONDITION, $e);

        Buffer::start();
        $this->setSimulatedGetData($meta->jsonSerialize());
        $jsCall = new JsCall('quicksearch', ['query' => 'ALQOADSFJ']);
        TestStorable2::onJsCall($jsCall);
        $this->assertTrue(str_contains(Buffer::get(), "<div"));

        $this->setSimulatedUser(['dev']);
        Buffer::start();
        $jsCall = new JsCall('quicksearch', ['query' => 'test']);
        TestStorable2::onJsCall($jsCall);
        $this->assertTrue(str_contains(Buffer::get(), "<div"));
    }
}
