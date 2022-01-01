<?php

namespace Form\Field;

use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Form\Field\Grid;
use Framelix\Framelix\Form\Field\Text;
use Framelix\Framelix\Lang;
use Framelix\FramelixTests\Storable\TestStorable1;
use Framelix\FramelixTests\TestCase;
use Throwable;

final class GridTest extends TestCase
{
    public function tests(): void
    {
        $field = new Grid();
        $field->name = $field::class;
        $field->required = true;

        $gridField = new Text();
        $gridField->name = "name";
        $field->addField($gridField);
        $field->removeField($gridField->name);
        $field->addField($gridField);

        $field->getVisibilityCondition()->equal('foo', 'bar');
        $this->assertFalse($field->isVisible());
        $this->assertTrue($field->validate());
        $field->getVisibilityCondition()->clear();
        $this->assertSame(Lang::get('__framelix_form_validation_required__'), $field->validate());

        // update name to prevent caching of converted submitted values
        $this->assertNull($field->getSubmittedValue());
        $field->name .= "1";
        $this->setSimulatedPostData([$field->name => ['rows' => ['1' => ['name' => 'foo']]]]);
        $this->assertSame(['1' => ['name' => 'foo']], $field->getSubmittedValue());
        $this->assertTrue($field->validate());
        $this->removeSimulatedFile($field->name);

        // validators
        $field->name .= "1";
        $field->minRows = 2;
        $this->setSimulatedPostData([$field->name => ['rows' => ['1' => ['name' => 'foo']]]]);
        $this->assertIsString($field->validate());
        $this->assertNull($field->getSubmittedDeletedKeys());
        $this->removeSimulatedFile($field->name);

        $field->name .= "1";
        $field->maxRows = 0;
        $field->minRows = null;
        $this->assertNull($field->getSubmittedDeletedKeys());
        $this->setSimulatedPostData(
            [$field->name => ['rows' => ['1' => ['name' => 'foo']], 'deleted' => [1 => 1, 2 => 1, 3 => 1]]]
        );
        $this->assertIsString($field->validate());
        $this->assertSame([1, 2, 3], $field->getSubmittedDeletedKeys());
        $this->removeSimulatedFile($field->name);
        $e = null;
        try {
            $field->addField($field);
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::FORM_GRID_NESTED_NOT_ALLOWED, $e);

        $field->defaultValue = [new TestStorable1()];
        $this->assertIsArray($field->jsonSerialize());
    }
}
