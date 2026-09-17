<?php

namespace wcf\system\importer;

use wcf\command\file\CreateFileFromExistingFile;
use wcf\data\file\File;
use wcf\data\file\FileBuilder;

/**
 * Import files.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractFileImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = File::class;

    /**
     * object type for `com.woltlab.wcf.file`
     */
    protected string $objectType;


    protected function importFile(string $fileLocation, ?string $filename = null): ?File
    {
        // check file location
        if (!\is_readable($fileLocation)) {
            return null;
        }

        $filename = $filename ?: \basename($fileLocation);
        $file = new CreateFileFromExistingFile(
            $fileLocation,
            $filename,
            $this->objectType,
            true
        )();

        if ($file === null) {
            return null;
        }

        if ($this->isValidFile($file)) {
            return $file;
        }

        return null;
    }

    abstract protected function isValidFile(File $file): bool;
}
