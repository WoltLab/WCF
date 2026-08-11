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
        foreach ($this->objectList->getObjects() as $file) {
            $file = $this->fixFile($file);

            try {
                $file = FileProcessor::getInstance()->stripExif($file);
                $file = FileProcessor::getInstance()->generateWebpVariant($file);
                $file = FileProcessor::getInstance()->convertImageFormat($file);
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
        $renamedFileIDs = [];
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

            $pathnameChanged = $previousFileExtension !== $detectedFileExtension;
            if ($pathnameChanged) {
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

                $renamedFileIDs[] = $file->fileID;
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

            foreach ($renamedFileIDs as $fileID) {
                $file = $this->objectList->search($fileID);
                \assert($file !== null);

                $file->getProcessor()?->sourceFilenameChanged($file);
            }
        }
    }

    #[\NoDiscard("as the file itself could change")]
    private function fixFile(File $file): File
    {
        if ($file->fileHashWebp === null) {
            return $file;
        }

        if (\file_exists($file->getPathname())) {
            return $file;
        }

        $pathnameWebp = $file->getPathnameWebp();
        if ($pathnameWebp === null || \file_exists($pathnameWebp)) {
            return $file;
        }

        // In some cases the database record is out of sync and the file has
        // been converted to WebP but is not recognized as such.
        $pathnameWebp = \str_replace('-variant.webp', '.webp', $pathnameWebp);
        if (!\file_exists($pathnameWebp)) {
            return $file;
        }

        // The file does exist but under its WebP filename.
        (new FileEditor($file))->update([
            'filename' => \sprintf(
                "%s.webp",
                \preg_replace(
                    '~\.(?:jpe?g|png)$~i',
                    '',
                    $file->filename,
                )
            ),
            'fileSize' => \filesize($pathnameWebp),
            'fileHash' => $file->fileHashWebp,
            'fileHashWebp' => null,
            'mimeType' => 'image/webp',
        ]);

        $updatedFile = new File($file->fileID);
        $updatedFile->getProcessor()?->sourceFilenameChanged($updatedFile);

        return $updatedFile;
    }

    private function getPath(string $fileHash, string $fileExtension): string
    {
        $folderA = \substr($fileHash, 0, 2);
        $folderB = \substr($fileHash, 2, 2);
        $isStaticFile = $fileExtension !== 'bin';

        return \sprintf(
            '%s_data/%s/files/%s/%s/',
            \WCF_DIR,
            $isStaticFile ? 'public' : 'private',
            $folderA,
            $folderB,
        );
    }
}
