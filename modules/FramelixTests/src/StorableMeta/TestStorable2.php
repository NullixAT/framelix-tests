<?php

namespace Framelix\FramelixUnitTests\StorableMeta;

use Framelix\Framelix\Form\Field\Email;
use Framelix\Framelix\Form\Field\Select;
use Framelix\Framelix\Form\Field\Textarea;
use Framelix\Framelix\Storable\Storable;
use Framelix\Framelix\StorableMeta;

/**
 * TestStorable2
 */
class TestStorable2 extends StorableMeta
{
    /**
     * The storable
     * @var \Framelix\FramelixUnitTests\Storable\TestStorable2
     */
    public Storable $storable;

    /**
     * Initialize this meta
     */
    protected function init(): void
    {
        $this->addDefaultPropertiesAtStart();

        $field = new Email();
        $property = $this->createProperty("name");
        $property->field = $field;
        $property->setLabel("name");

        $field = new Email();
        $property = $this->createProperty("notExisting");
        $property->field = $field;
        $property->setLabel("notExisting");

        $field = new Email();
        $property = $this->createProperty("longTextOptional");
        $property->field = $field;
        $property->setVisibility(null, false);
        $property->setLabel("invisible");

        $field = new Select();
        $property = $this->createProperty("systemValueOptional");
        $property->field = $field;
        $property->setLabel("systemValueOptional");

        $field = new Textarea();
        $property = $this->createProperty("longText");
        $property->field = $field;
        $property->setLabel("longText");
        $property->setLabelDescription("longText");
        $property->lazySearchConditionColumns->addColumn('longText', 'longText', 'string');

        $this->addDefaultPropertiesAtEnd();
    }
}