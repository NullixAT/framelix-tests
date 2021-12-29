<?php

namespace Framelix\FramelixUnitTests\Storable;

use Framelix\Framelix\Date;
use Framelix\Framelix\DateTime;
use Framelix\Framelix\Db\StorableSchema;
use Framelix\Framelix\Storable\StorableExtended;
use Framelix\Framelix\Time;

/**
 * TestStorable2
 * @property string $name
 * @property string $longText
 * @property string|null $longTextLazy
 * @property string|null $longTextOptional
 * @property int $intNumber
 * @property int|null $intNumberOptional
 * @property float $floatNumber
 * @property float|null $floatNumberOptional
 * @property bool $boolFlag
 * @property bool|null $boolFlagOptional
 * @property mixed $jsonData
 * @property mixed|null $jsonDataOptional
 * @property Time|null $time
 * @property int[]|null $typedIntArray
 * @property float[]|null $typedFloatArray
 * @property string[]|null $typedStringArray
 * @property bool[]|null $typedBoolArray
 * @property DateTime[]|null $typedDateArray
 * @property TestStorable2|null $selfReferenceOptional
 * @property TestStorable1|null $otherReferenceOptional
 * @property TestStorable1[]|null $otherReferenceArrayOptional
 * @property DateTime $dateTime
 * @property DateTime|null $dateTimeOptional
 * @property Date $date
 * @property DateTime|null $dateOptional
 */
class TestStorable2 extends StorableExtended
{
    /**
     * Setup self storable meta
     * @param StorableSchema $selfStorableSchema
     */
    protected static function setupStorableSchema(StorableSchema $selfStorableSchema): void
    {
        parent::setupStorableSchema($selfStorableSchema);
        $selfStorableSchema->connectionId = "test";
        $storableSchemaProperty = $selfStorableSchema->properties['floatNumberOptional'];
        $storableSchemaProperty->dbComment = "Some comment";
        $storableSchemaProperty->length = 11;
        $storableSchemaProperty->decimals = 3;
        $storableSchemaProperty = $selfStorableSchema->properties['longText'];
        $storableSchemaProperty->databaseType = "longtext";
        $storableSchemaProperty->length = null;
        $storableSchemaProperty = $selfStorableSchema->properties['longTextLazy'];
        $storableSchemaProperty->databaseType = "longtext";
        $storableSchemaProperty->length = null;
        $storableSchemaProperty->lazyFetch = true;
        $storableSchemaProperty = $selfStorableSchema->properties['longTextOptional'];
        $storableSchemaProperty->databaseType = "longtext";
        $storableSchemaProperty->length = null;
        $storableSchemaProperty = $selfStorableSchema->properties['date'];
        $storableSchemaProperty->databaseType = "date";
        $storableSchemaProperty = $selfStorableSchema->properties['dateOptional'];
        $storableSchemaProperty->databaseType = "date";
        $selfStorableSchema->addIndex('longText', 'fulltext');
    }

    /**
     * Is this storable deletable
     * @return bool
     */
    public function isDeletable(): bool
    {
        return true;
    }
}