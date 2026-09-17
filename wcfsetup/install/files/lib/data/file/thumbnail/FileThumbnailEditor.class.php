<?php

namespace wcf\data\file\thumbnail;

use wcf\command\file\thumbnail\CreateThumbnailFromTemporaryFile;
use wcf\data\DatabaseObjectEditor;
use wcf\data\file\File;
use wcf\system\file\processor\ThumbnailFormat;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @deprecated 6.3 Use `FileThumbnailBuilder` instead.
 *
 * @mixin FileThumbnail
 * @extends DatabaseObjectEditor<FileThumbnail>
 */
class FileThumbnailEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = FileThumbnail::class;

    public function deleteFiles(): void
    {
        @\unlink($this->getPathname());
    }

    #[\Override]
    public static function deleteAll(array $objectIDs = [])
    {
        if ($objectIDs === []) {
            return 0;
        }

        FileThumbnailBuilder::deleteAll($objectIDs);

        return \count($objectIDs);
    }

    public static function createFromTemporaryFile(
        File $file,
        ThumbnailFormat $format,
        string $filename
    ): FileThumbnail {
        return new CreateThumbnailFromTemporaryFile($file, $format, $filename)();
    }
}
