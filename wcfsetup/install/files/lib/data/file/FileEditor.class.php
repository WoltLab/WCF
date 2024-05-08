<?php

namespace wcf\data\file;

use wcf\data\DatabaseObjectEditor;
use wcf\data\file\temporary\FileTemporary;
use wcf\data\file\thumbnail\FileThumbnailEditor;
use wcf\data\file\thumbnail\FileThumbnailList;
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

    public function deleteFiles(): void
    {
        @\unlink($this->getPathname());

        $thumbnailIDs = \array_column($this->getThumbnails(), 'thumbnailID');
        if ($thumbnailIDs !== []) {
            FileThumbnailEditor::deleteAll($thumbnailIDs);
        }
    }

    public static function deleteAll(array $objectIDs = [])
    {
        $fileList = new FileList();
        $fileList->getConditionBuilder()->add("fileID IN (?)", [$objectIDs]);
        $fileList->readObjects();
        $files = $fileList->getObjects();
        if (\count($files) === 0) {
            return 0;
        }

        $thumbnailList = new FileThumbnailList();
        $thumbnailList->getConditionBuilder()->add("fileID IN (?)", [$objectIDs]);
        $thumbnailList->readObjects();
        foreach ($thumbnailList as $thumbnail) {
            $files[$thumbnail->fileID]->addThumbnail($thumbnail);
        }

        foreach ($files as $file) {
            (new FileEditor($file))->deleteFiles();
        }

        return parent::deleteAll($objectIDs);
    }

    public static function createFromTemporary(FileTemporary $fileTemporary): File
    {
        $pathname = $fileTemporary->getPathname();
        $mimeType = FileUtil::getMimeType($pathname);
        $isImage = match ($mimeType) {
            'image/gif' => true,
            'image/jpeg' => true,
            'image/png' => true,
            'image/webp' => true,
            default => false,
        };

        $width = $height = null;
        if ($isImage) {
            [$width, $height] = \getimagesize($pathname);
        }

        $fileAction = new FileAction([], 'create', ['data' => [
            'filename' => $fileTemporary->filename,
            'fileSize' => $fileTemporary->fileSize,
            'fileHash' => $fileTemporary->fileHash,
            'fileExtension' => File::getSafeFileExtension($mimeType, $fileTemporary->filename),
            'secret' => \bin2hex(\random_bytes(10)),
            'objectTypeID' => $fileTemporary->objectTypeID,
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
            $pathname,
            $filePath . $file->getSourceFilename()
        );

        return $file;
    }
}
