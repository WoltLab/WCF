<?php

namespace wcf\data\file;

use wcf\data\DatabaseObjectEditor;
use wcf\data\file\temporary\FileTemporary;

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
        $fileAction = new FileAction([], 'create', ['data' => [
            'filename' => $fileTemporary->filename,
            'fileSize' => $fileTemporary->fileSize,
            'fileHash' => $fileTemporary->fileHash,
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
