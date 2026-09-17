<?php

namespace wcf\data\file;

use wcf\command\file\CreateFileFromExistingFile;
use wcf\command\file\CreateFileFromTemporary;
use wcf\data\DatabaseObjectEditor;
use wcf\data\file\temporary\FileTemporary;
use wcf\data\file\thumbnail\FileThumbnailEditor;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @deprecated 6.3 Use `FileBuilder` instead.
 *
 * @mixin File
 * @extends DatabaseObjectEditor<File>
 */
class FileEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = File::class;

    public function deleteFiles(): void
    {
        @\unlink($this->getPathname());

        $pathnameWebp = $this->getPathnameWebp();
        if ($pathnameWebp !== null) {
            @\unlink($pathnameWebp);
        }

        $thumbnailIDs = \array_column($this->getThumbnails(), 'thumbnailID');
        if ($thumbnailIDs !== []) {
            FileThumbnailEditor::deleteAll($thumbnailIDs);
        }
    }

    #[\Override]
    public static function deleteAll(array $objectIDs = [])
    {
        if ($objectIDs === []) {
            return 0;
        }

        FileBuilder::deleteAll($objectIDs);

        return \count($objectIDs);
    }

    public static function createFromTemporary(FileTemporary $fileTemporary): File
    {
        return new CreateFileFromTemporary($fileTemporary)();
    }

    /**
     * @param null|array<string, array<string, mixed>> $exifData
     */
    public static function createFromExistingFile(
        string $pathname,
        string $originalFilename,
        string $objectTypeName,
        bool $copy = false,
        ?int $uploadTime = null,
        ?array $exifData = null,
    ): ?File {
        return new CreateFileFromExistingFile(
            $pathname,
            $originalFilename,
            $objectTypeName,
            $copy,
            $uploadTime,
            $exifData,
        )();
    }
}
