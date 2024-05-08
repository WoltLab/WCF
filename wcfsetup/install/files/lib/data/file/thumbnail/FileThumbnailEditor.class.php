<?php

namespace wcf\data\file\thumbnail;

use wcf\data\DatabaseObjectEditor;
use wcf\data\file\File;
use wcf\system\file\processor\ThumbnailFormat;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @method static FileThumbnail create(array $parameters = [])
 * @method FileThumbnail getDecoratedObject()
 * @mixin FileThumbnail
 */
class FileThumbnailEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = FileThumbnail::class;

    public function deleteFiles(): void
    {
        @\unlink($this->getPath() . $this->getSourceFilename());
    }

    public static function deleteAll(array $objectIDs = [])
    {
        $thumbnailList = new FileThumbnailList();
        $thumbnailList->getConditionBuilder()->add("thumbnailID IN (?)", [$objectIDs]);
        $thumbnailList->readObjects();

        if (\count($thumbnailList) === 0) {
            return 0;
        }

        foreach ($thumbnailList as $thumbnail) {
            (new FileThumbnailEditor($thumbnail))->deleteFiles();
        }

        return parent::deleteAll($objectIDs);
    }

    public static function createFromTemporaryFile(
        File $file,
        ThumbnailFormat $format,
        string $filename
    ): FileThumbnail {
        [$width, $height] = \getimagesize($filename);

        $action = new FileThumbnailAction([], 'create', [
            'data' => [
                'fileID' => $file->fileID,
                'identifier' => $format->identifier,
                'fileHash' => \hash_file('sha256', $filename),
                'fileExtension' => 'webp',
                'width' => $width,
                'height' => $height,
            ],
        ]);
        $fileThumbnail = $action->executeAction()['returnValues'];
        \assert($fileThumbnail instanceof FileThumbnail);

        $filePath = $fileThumbnail->getPath();
        if (!\is_dir($filePath)) {
            \mkdir($filePath, recursive: true);
        }

        \rename(
            $filename,
            $filePath . $fileThumbnail->getSourceFilename()
        );

        return $fileThumbnail;
    }
}
