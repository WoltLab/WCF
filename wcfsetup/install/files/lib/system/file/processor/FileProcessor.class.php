<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\data\file\thumbnail\FileThumbnail;
use wcf\data\file\thumbnail\FileThumbnailEditor;
use wcf\data\file\thumbnail\FileThumbnailList;
use wcf\system\event\EventHandler;
use wcf\system\file\processor\event\FileProcessorCollecting;
use wcf\system\image\adapter\ImageAdapter;
use wcf\system\image\ImageHandler;
use wcf\system\SingletonFactory;
use wcf\util\FileUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class FileProcessor extends SingletonFactory
{
    /**
     * @var array<string, IFileProcessor>
     */
    private array $processors;

    #[\Override]
    public function init(): void
    {
        $event = new FileProcessorCollecting();
        EventHandler::getInstance()->fire($event);
        $this->processors = $event->getProcessors();
    }

    public function forTypeName(string $typeName): ?IFileProcessor
    {
        return $this->processors[$typeName] ?? null;
    }

    public function getHtmlElement(IFileProcessor $fileProcessor, array $context): string
    {
        $allowedFileExtensions = $fileProcessor->getAllowedFileExtensions($context);
        if (\in_array('*', $allowedFileExtensions)) {
            $allowedFileExtensions = '';
        } else {
            $allowedFileExtensions = \implode(
                ',',
                \array_map(
                    static fn (string $fileExtension) => ".{$fileExtension}",
                    $allowedFileExtensions
                )
            );
        }

        return \sprintf(
            <<<'HTML'
                <woltlab-core-file-upload
                    data-type-name="%s"
                    data-context="%s"
                    data-file-extensions="%s"
                ></woltlab-core-file-upload>
                HTML,
            StringUtil::encodeHTML($fileProcessor->getTypeName()),
            StringUtil::encodeHTML(JSON::encode($context)),
            StringUtil::encodeHTML($allowedFileExtensions),
        );
    }

    public function generateThumbnails(File $file): void
    {
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
            if (isset($existingThumbnails[$format->identifier])) {
                continue;
            }

            if ($imageAdapter === null) {
                $imageAdapter = ImageHandler::getInstance()->getAdapter();
                $imageAdapter->loadFile($file->getPath() . $file->getSourceFilename());
            }

            assert($imageAdapter instanceof ImageAdapter);
            $image = $imageAdapter->createThumbnail($format->width, $format->height, $format->retainDimensions);

            $filename = FileUtil::getTemporaryFilename();
            $imageAdapter->writeImage($image, $filename);

            $fileThumbnail = FileThumbnailEditor::createFromTemporaryFile($file, $format, $filename);
            $processor->adoptThumbnail($fileThumbnail);
        }
    }
}
