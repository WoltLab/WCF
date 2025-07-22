<?php

namespace wcf\system\worker;

use wcf\data\file\File;
use wcf\data\file\FileEditor;
use wcf\data\file\FileList;
use wcf\system\file\processor\exception\DamagedImage;
use wcf\system\file\processor\FileProcessor;
use wcf\util\FileUtil;

use function wcf\functions\exception\logThrowable;

/**
 * Worker implementation for updating files.
 *
 * @author Alexander Ebert
 * @copyright 2001-2014 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @extends AbstractLinearRebuildDataWorker<FileList>
 */
final class FileRebuildDataWorker extends AbstractLinearRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = FileList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 10;

    #[\Override]
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlOrderBy = 'file.fileID';
    }

    #[\Override]
    public function execute()
    {
        parent::execute();

        $this->fixMimeType();

        $damagedFileIDs = [];
        foreach ($this->objectList as $file) {
            try {
                FileProcessor::getInstance()->generateWebpVariant($file);
                FileProcessor::getInstance()->generateThumbnails($file);
            } catch (DamagedImage $e) {
                logThrowable($e);

                $damagedFileIDs[] = $e->fileID;
            }
        }

        if ($damagedFileIDs !== []) {
            FileEditor::deleteAll($damagedFileIDs);
        }
    }

    private function fixMimeType(): void
    {
        $reloadFiles = false;
        foreach ($this->objectList as $file) {
            // Workaround for images that have been detected but failed to
            // determine their dimensions.
            $isImageWithoutDimensions = $file->isImage() && $file->width === null;

            if ($file->mimeType !== 'application/octet-stream' && !$isImageWithoutDimensions) {
                continue;
            }

            $mimeType = FileUtil::getMimeType($file->getPathname());
            if ($file->mimeType === $mimeType && !$isImageWithoutDimensions) {
                continue;
            }

            // When the mime type was incorrectly detected before, for example,
            // because fileinfo was not present or malfunctioning, the physical
            // location of the file may be incorrect.
            //
            // The location is determined by the safe file extension, anything
            // that ends in `.bin` is piped through PHP instead of being served
            // through the web server directly.
            $previousFileExtension = File::getSafeFileExtension($file->mimeType, $file->filename);
            $detectedFileExtension = File::getSafeFileExtension($mimeType, $file->filename);

            $width = $height = null;
            if (\str_starts_with($mimeType, 'image/')) {
                $data = @\getimagesize($file->getPathname());
                if ($data === false) {
                    // Treat broken images as binary files.
                    $mimeType = 'application/octet-stream';
                    $detectedFileExtension = 'bin';

                    if ($file->mimeType === $mimeType) {
                        continue;
                    }
                } else {
                    $width = $data[0];
                    $height = $data[1];
                }
            }

            if ($previousFileExtension !== $detectedFileExtension) {
                $path = $this->getPath($file->fileHash, $detectedFileExtension);
                FileUtil::makePath($path);

                \rename(
                    $file->getPathname(),
                    $path . \sprintf(
                        '%d-%s.%s',
                        $file->fileID,
                        $file->fileHash,
                        $detectedFileExtension,
                    ),
                );
            }

            (new FileEditor($file))->update([
                'fileExtension' => $detectedFileExtension,
                'mimeType' => $mimeType,
                'width' => $width,
                'height' => $height,
            ]);

            $reloadFiles = true;
        }

        if ($reloadFiles) {
            $this->objectList->readObjects();
        }
    }

    private function getPath(string $fileHash, string $fileExtension): string
    {
        $folderA = \substr($fileHash, 0, 2);
        $folderB = \substr($fileHash, 2, 2);
        $isStaticFile = $fileExtension !== 'bin';

        return \sprintf(
            '_data/%s/files/%s/%s/',
            $isStaticFile ? 'public' : 'private',
            $folderA,
            $folderB,
        );
    }
}
