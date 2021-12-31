<?php

namespace Framelix\FramelixUnitTests\Storable\Deeper;

use Framelix\Framelix\Db\StorableSchema;
use Framelix\FramelixUnitTests\Storable;

/**
 * TestStorableDeeper
 * Just testing property types where class name is on another level from use
 * @property Storable\TestStorable2|null $selfReferenceOptional
 */
class TestStorableDeeper extends \Framelix\Framelix\Storable\Storable
{
    /**
     * Setup self storable meta
     * @param StorableSchema $selfStorableSchema
     */
    protected static function setupStorableSchema(StorableSchema $selfStorableSchema): void
    {
        parent::setupStorableSchema($selfStorableSchema);
        $selfStorableSchema->connectionId = "test";
    }
}