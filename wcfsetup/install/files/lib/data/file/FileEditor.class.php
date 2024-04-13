<?php

namespace wcf\data\file;

use wcf\data\DatabaseObjectEditor;
use wcf\data\file\temporary\FileTemporary;
use wcf\util\FileUtil;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @method static File create(array $parameters = [])
 * @method File getDecoratedObject()
 * @mixin File
 */
class FileEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = File::class;

    public static function createFromTemporary(FileTemporary $fileTemporary): File
    {
        $mimeType = FileUtil::getMimeType($fileTemporary->getPath() . $fileTemporary->getFilename());
        $isImage = match ($mimeType) {
            'image/gif' => true,
            'image/jpeg' => true,
            'image/png' => true,
            'image/webp' => true,
            default => false,
        };

        $width = $height = null;
        if ($isImage) {
            [$width, $height] = \getimagesize($fileTemporary->getPath() . $fileTemporary->getFilename());
        }

        $fileAction = new FileAction([], 'create', ['data' => [
            'filename' => $fileTemporary->filename,
            'fileSize' => $fileTemporary->fileSize,
            'fileHash' => $fileTemporary->fileHash,
            'typeName' => $fileTemporary->typeName,
            'mimeType' => $mimeType,
            'width' => $width,
            'height' => $height,
        ]]);
        $file = $fileAction->executeAction()['returnValues'];
        \assert($file instanceof File);

        $filePath = $file->getPath();
        if (!\is_dir($filePath)) {
            \mkdir($filePath, recursive: true);
        }

        \rename(
            $fileTemporary->getPath() . $fileTemporary->getFilename(),
            $filePath . $file->getSourceFilename()
        );

        return $file;
    }
}
