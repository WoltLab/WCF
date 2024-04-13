<?php

namespace wcf\data\file\thumbnail;

use wcf\data\DatabaseObjectEditor;
use wcf\data\file\File;
use wcf\system\file\processor\ThumbnailFormat;
use wcf\util\FileUtil;

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
                'fileHash' => hash_file('sha256', $filename),
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
