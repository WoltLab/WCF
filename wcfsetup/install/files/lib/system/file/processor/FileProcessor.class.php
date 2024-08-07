<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\data\file\FileEditor;
use wcf\data\file\thumbnail\FileThumbnail;
use wcf\data\file\thumbnail\FileThumbnailEditor;
use wcf\data\file\thumbnail\FileThumbnailList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\file\processor\exception\DamagedImage;
use wcf\system\image\adapter\exception\ImageNotProcessable;
use wcf\system\image\adapter\exception\ImageNotReadable;
use wcf\system\image\adapter\ImageAdapter;
use wcf\system\image\ImageHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

use function wcf\functions\exception\logThrowable;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class FileProcessor extends SingletonFactory
{
    public const MAXIMUM_NUMBER_OF_CHUNKS = 255;

    /**
     * @var array<string, ObjectType>
     */
    private array $objectTypes;

    #[\Override]
    public function init(): void
    {
        $this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.file');
    }

    public function getProcessorByName(string $objectType): ?IFileProcessor
    {
        return $this->getObjectType($objectType)?->getProcessor();
    }

    public function getProcessorById(?int $objectTypeID): ?IFileProcessor
    {
        if ($objectTypeID === null) {
            return null;
        }

        foreach ($this->objectTypes as $objectType) {
            if ($objectType->objectTypeID === $objectTypeID) {
                return $objectType->getProcessor();
            }
        }

        return null;
    }

    public function getObjectType(string $objectType): ?ObjectType
    {
        return $this->objectTypes[$objectType] ?? null;
    }

    public function getHtmlElement(IFileProcessor $fileProcessor, array $context): string
    {
        $allowedFileExtensions = $fileProcessor->getAllowedFileExtensions($context);
        if (\in_array('*', $allowedFileExtensions)) {
            $allowedFileExtensions = '';
        } else {
            // The `accept` attribute of `input[type="file"]` is a bit weird and
            // only validates against the string to the right of the last
            // period. This means an extension `.tar.gz` can never match.
            $allowedFileExtensions = \array_unique(
                \array_map(
                    static fn(string $fileExtension) => \preg_replace('~.*?([^.]+)$~', '\\1', $fileExtension),
                    $allowedFileExtensions,
                ),
            );

            $allowedFileExtensions = \implode(
                ',',
                \array_map(
                    static fn(string $fileExtension) => ".{$fileExtension}",
                    $allowedFileExtensions
                )
            );
        }

        $maximumCount = $fileProcessor->getMaximumCount($context);
        if ($maximumCount === null) {
            $maximumCount = -1;
        }

        $maximumSize = $fileProcessor->getMaximumSize($context);
        if ($maximumSize === null) {
            $maximumSize = -1;
        }

        return \sprintf(
            <<<'HTML'
                <woltlab-core-file-upload
                    data-object-type="%s"
                    data-context="%s"
                    data-file-extensions="%s"
                    data-resize-configuration="%s"
                    data-maximum-count="%d"
                    data-maximum-size="%d"
                ></woltlab-core-file-upload>
                HTML,
            StringUtil::encodeHTML($fileProcessor->getObjectTypeName()),
            StringUtil::encodeHTML(JSON::encode($context)),
            StringUtil::encodeHTML($allowedFileExtensions),
            StringUtil::encodeHTML(JSON::encode($fileProcessor->getResizeConfiguration())),
            $maximumCount,
            $maximumSize,
        );
    }

    public function generateWebpVariant(File $file): void
    {
        $canGenerateThumbnail = match ($file->mimeType) {
            'image/jpeg', 'image/png' => true,
            default => false,
        };

        if (!$canGenerateThumbnail) {
            if ($file->fileHashWebp !== null) {
                (new FileEditor($file))->update([
                    'fileHashWebp' => null,
                ]);
            }

            return;
        }

        if ($file->fileHashWebp !== null) {
            $pathname = $file->getPathnameWebp();
            if (\file_exists($pathname) && \hash_file('sha256', $pathname) === $file->fileHashWebp) {
                return;
            }
        }

        $imageAdapter = ImageHandler::getInstance()->getAdapter();

        try {
            $imageAdapter->loadSingleFrameFromFile($file->getPathname());
        } catch (SystemException | ImageNotReadable) {
            throw new DamagedImage($file->fileID);
        } catch (ImageNotProcessable $e) {
            logThrowable($e);

            return;
        }

        $filename = FileUtil::getTemporaryFilename(extension: 'webp');
        $imageAdapter->saveImageAs($imageAdapter->getImage(), $filename, 'webp', 80);

        (new FileEditor($file))->update([
            'fileHashWebp' => \hash_file('sha256', $filename),
        ]);

        $file = new File($file->fileID);

        $pathname = $file->getPathnameWebp();
        \assert($pathname !== null);

        \rename($filename, $pathname);
    }

    public function generateThumbnails(File $file): void
    {
        if (!$file->isImage()) {
            return;
        }

        $processor = $file->getProcessor();
        if ($processor === null) {
            return;
        }

        $formats = $processor->getThumbnailFormats();
        if ($formats === []) {
            return;
        }

        $thumbnailList = new FileThumbnailList();
        $thumbnailList->getConditionBuilder()->add("fileID = ?", [$file->fileID]);
        $thumbnailList->readObjects();

        $existingThumbnails = [];
        foreach ($thumbnailList as $thumbnail) {
            \assert($thumbnail instanceof FileThumbnail);
            $existingThumbnails[$thumbnail->identifier] = $thumbnail;
        }

        $imageAdapter = null;
        foreach ($formats as $format) {
            $existingThumbnail = $existingThumbnails[$format->identifier] ?? null;

            // Check if we the source image is larger than the dimensions of the
            // requested thumbnails.
            if ($format->width > $file->width && $format->height > $file->height) {
                // There currently is a thumbnail for this format but the
                // conditions for its existence are no longer met.
                if ($existingThumbnail !== null) {
                    FileThumbnailEditor::deleteAll([$existingThumbnail->thumbnailID]);
                }

                continue;
            }

            if ($existingThumbnail !== null) {
                if ($existingThumbnail->needsRebuild($format)) {
                    // There currently is a thumbnail but it is no longer valid.
                    FileThumbnailEditor::deleteAll([$existingThumbnail->thumbnailID]);
                } else {
                    // This thumbnail is still fine.
                    continue;
                }
            }

            if ($imageAdapter === null) {
                $imageAdapter = ImageHandler::getInstance()->getAdapter();

                try {
                    $imageAdapter->loadSingleFrameFromFile($file->getPathname());
                } catch (SystemException | ImageNotReadable $e) {
                    throw new DamagedImage($file->fileID, $e);
                } catch (ImageNotProcessable $e) {
                    logThrowable($e);

                    return;
                }
            }

            \assert($imageAdapter instanceof ImageAdapter);

            try {
                $image = $imageAdapter->createThumbnail($format->width, $format->height, $format->retainDimensions);
            } catch (\Throwable $e) {
                logThrowable($e);

                continue;
            }

            $filename = FileUtil::getTemporaryFilename(extension: 'webp');
            $imageAdapter->saveImageAs($image, $filename, 'webp', 80);

            $fileThumbnail = FileThumbnailEditor::createFromTemporaryFile($file, $format, $filename);
            $processor->adoptThumbnail($fileThumbnail);
        }
    }

    public function delete(array $files): void
    {
        $fileIDs = \array_column($files, 'fileID');

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add('fileID IN (?)', [$fileIDs]);

        $sql = "SELECT  thumbnailID
                FROM    wcf1_file_thumbnail
                {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $thumbnailIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($this->objectTypes as $objectType) {
            $objectType->getProcessor()->delete($fileIDs, $thumbnailIDs);
        }
    }

    public function hasReachedUploadLimit(IFileProcessor $fileProcessor, array $context): bool
    {
        $isReplacement = $context['__replace'] ?? false;
        if ($isReplacement) {
            return false;
        }

        $existingFiles = $fileProcessor->countExistingFiles($context);
        if ($existingFiles === null) {
            return false;
        }

        $maximumCount = $fileProcessor->getMaximumCount($context);

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_file_temporary
                WHERE   objectTypeID = ?
                    AND context = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->getObjectType($fileProcessor->getObjectTypeName())->objectTypeID,
            JSON::encode($context),
        ]);
        $numberOfTemporaryFiles = $statement->fetchSingleColumn();
        if ($existingFiles + $numberOfTemporaryFiles >= $maximumCount) {
            return true;
        }

        return false;
    }

    public function getOptimalChunkSize(): int
    {
        $postMaxSize = \ini_parse_quantity(\ini_get('post_max_size'));
        if ($postMaxSize === 0) {
            // Disabling it is fishy, assume a more reasonable limit of 100 MB.
            $postMaxSize = 100_000_000;
        }

        return $postMaxSize;
    }

    public function getMaximumFileSize(): int
    {
        $maximumFileSize = $this->getOptimalChunkSize() * self::MAXIMUM_NUMBER_OF_CHUNKS;
        if (\defined('ENTERPRISE_MODE_MAXIMUM_FILE_SIZE')) {
            $maximumFileSize = \min($maximumFileSize, \constant('ENTERPRISE_MODE_MAXIMUM_FILE_SIZE'));
        }

        return $maximumFileSize;
    }

    public function copy(File $oldFile, string $objectType): File
    {
        $objectTypeObj = $this->getObjectType($objectType);
        if ($objectTypeObj === null) {
            throw new \InvalidArgumentException("The object type '{$objectType}' is invalid.");
        }

        $newFile = FileEditor::create([
            'filename' => $oldFile->filename,
            'fileSize' => $oldFile->fileSize,
            'fileHash' => $oldFile->fileHash,
            'fileExtension' => $oldFile->fileExtension,
            'objectTypeID' => $objectTypeObj->objectTypeID,
            'mimeType' => $oldFile->mimeType,
            'width' => $oldFile->width,
            'height' => $oldFile->height,
            'fileHashWebp' => $oldFile->fileHashWebp,
        ]);

        \copy($oldFile->getPathname(), $newFile->getPathname());

        if ($oldFile->fileHashWebp !== null) {
            \copy($oldFile->getPathnameWebp(), $newFile->getPathnameWebp());
        }

        $this->copyThumbnails($oldFile->fileID, $newFile->fileID);

        return $newFile;
    }

    private function copyThumbnails(int $oldFileID, int $newFileID): void
    {
        $thumbnailList = new FileThumbnailList();
        $thumbnailList->getConditionBuilder()->add("fileID = ?", [$oldFileID]);
        $thumbnailList->readObjects();

        foreach ($thumbnailList as $oldThumbnail) {
            $newThumbnail = FileThumbnailEditor::create([
                'fileID' => $newFileID,
                'identifier' => $oldThumbnail->identifier,
                'fileHash' => $oldThumbnail->fileHash,
                'fileExtension' => $oldThumbnail->fileExtension,
                'width' => $oldThumbnail->width,
                'height' => $oldThumbnail->height,
                'formatChecksum' => $oldThumbnail->formatChecksum,
            ]);

            \copy(
                $oldThumbnail->getPath() . $oldThumbnail->getSourceFilename(),
                $newThumbnail->getPath() . $newThumbnail->getSourceFilename(),
            );
        }
    }
}
