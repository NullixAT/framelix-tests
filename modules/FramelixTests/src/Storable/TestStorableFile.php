<?php

namespace Framelix\FramelixUnitTests\Storable;

use Framelix\Framelix\Db\StorableSchema;
use Framelix\Framelix\Storable\StorableFile;

/**
 * TestStorableFile
 */
class TestStorableFile extends StorableFile
{
    /**
     * The folder on disk to store the file in
     * The system does create more folders in this folder, to separate files, based on $maxFilesPerFolder setting
     * @var string|null
     */
    public ?string $folder = __DIR__ . "/../../tmp/storablefiletest";

    /**
     * Setup self storable meta
     * @param StorableSchema $selfStorableSchema
     */
    protected static function setupStorableSchema(StorableSchema $selfStorableSchema): void
    {
        parent::setupStorableSchema($selfStorableSchema);
        $selfStorableSchema->connectionId = "test";
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