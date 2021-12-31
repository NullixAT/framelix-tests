<?php

namespace Framelix\FramelixUnitTests\Storable;

use Framelix\Framelix\DateTime;
use Framelix\Framelix\Db\StorableSchema;
use Framelix\Framelix\Storable\Storable;

/**
 * TestStorable1
 * @property string $name
 * @property string $longText
 * @property string|null $longTextOptional
 * @property int $intNumber
 * @property int|null $intNumberOptional
 * @property float $floatNumber
 * @property float|null $floatNumberOptional
 * @property bool $boolFlag
 * @property bool|null $boolFlagOptional
 * @property mixed $jsonData
 * @property mixed|null $jsonDataOptional
 * @property TestStorable1|null $selfReferenceOptional
 * @property TestStorable2|null $otherReferenceOptional
 * @property DateTime $dateTime
 * @property DateTime|null $dateTimeOptional
 * @property DateTime $date
 * @property DateTime|null $dateOptional
 */
class TestStorable1 extends Storable
{
    /**
     * Setup self storable meta
     * @param StorableSchema $selfStorableSchema
     */
    protected static function setupStorableSchema(StorableSchema $selfStorableSchema): void
    {
        parent::setupStorableSchema($selfStorableSchema);
        $selfStorableSchema->connectionId = "test";
        $storableSchemaProperty = $selfStorableSchema->properties['longText'];
        $storableSchemaProperty->databaseType = "longtext";
        $storableSchemaProperty->length = null;
        $storableSchemaProperty = $selfStorableSchema->properties['longTextOptional'];
        $storableSchemaProperty->databaseType = "longtext";
        $storableSchemaProperty->length = null;
        $storableSchemaProperty = $selfStorableSchema->properties['date'];
        $storableSchemaProperty->databaseType = "date";
        $storableSchemaProperty = $selfStorableSchema->properties['dateOptional'];
        $storableSchemaProperty->databaseType = "date";
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