<?php

namespace Form\Field;

use Framelix\Framelix\Form\Field\File;
use Framelix\Framelix\Lang;
use Framelix\Framelix\Network\UploadedFile;
use Framelix\FramelixTests\Storable\TestStorableFile;
use Framelix\FramelixTests\TestCase;

final class FileTest extends TestCase
{
    public function tests(): void
    {
        $field = new File();
        $field->name = $field::class;
        $field->required = true;
        $this->callFormFieldDefaultMethods($field);

        $this->setSimulatedPostData([$field->name => "#aaaaaa"]);
        $this->assertSame(Lang::get('__framelix_form_validation_required__'), $field->validate());

        // update name to prevent caching of converted submitted values
        $field->name .= "1";
        $this->addSimulatedFile($field->name, 'test', false);
        $this->assertInstanceOf(UploadedFile::class, $field->getConvertedSubmittedValue()[0]);
        $this->assertTrue($field->validate());
        $this->removeSimulatedFile($field->name);

        // validators
        $field->name .= "1";
        $field->minSelectedFiles = 2;
        $this->addSimulatedFile($field->name, 'test', false);
        $this->assertIsString($field->validate());
        $this->removeSimulatedFile($field->name);

        $field->name .= "1";
        $field->maxSelectedFiles = 1;
        $field->minSelectedFiles = null;
        $this->addSimulatedFile($field->name, 'test', true);
        $this->assertIsString($field->validate());
        $this->removeSimulatedFile($field->name);

        $field->defaultValue = [new TestStorableFile()];
        $this->assertIsArray($field->jsonSerialize());
    }
}
