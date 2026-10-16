<?php

namespace wcf\system\file\processor;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\command\media\CreateMedia;
use wcf\data\file\File;
use wcf\data\media\Media;
use wcf\data\media\MediaBuilder;
use wcf\data\media\MediaList;
use wcf\event\media\MediaDeleted;
use wcf\http\Helper;
use wcf\system\cache\runtime\MediaRuntimeCache;
use wcf\system\category\CategoryHandler;
use wcf\system\event\EventHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * File processor for media files.
 *
 * Uploading a new file creates a media file, a file that replaces the file of
 * an existing media file (`mediaID` in the context) is assigned by the form
 * that edits the media file.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MediaFileProcessor extends AbstractFileProcessor
{
    private const IMAGE_EXTENSIONS = ['gif', 'jpg', 'jpeg', 'png', 'webp'];

    #[\Override]
    public function getObjectTypeName(): string
    {
        return 'com.woltlab.wcf.media';
    }

    #[\Override]
    public function getAllowedFileExtensions(array $context): array
    {
        $parameters = $this->mapContext($context);
        if ($parameters === null) {
            return [];
        }

        $media = $this->getMediaFromContext($parameters);
        if ($media !== null) {
            $extension = \mb_strtolower(\pathinfo($media->filename, \PATHINFO_EXTENSION));

            return [$extension !== '' ? $extension : '*'];
        }

        if ($parameters->imagesOnly) {
            return self::IMAGE_EXTENSIONS;
        }

        return ['*'];
    }

    #[\Override]
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult
    {
        if (!WCF::getSession()->hasPermission('admin.content.cms.canManageMedia')) {
            return FileProcessorPreflightResult::InsufficientPermissions;
        }

        $parameters = $this->mapContext($context);
        if ($parameters === null) {
            return FileProcessorPreflightResult::InvalidContext;
        }

        if ($parameters->mediaID !== 0) {
            $media = $this->getMediaFromContext($parameters);
            if ($media === null) {
                return FileProcessorPreflightResult::InvalidContext;
            }

            if (!$media->canManage()) {
                return FileProcessorPreflightResult::InsufficientPermissions;
            }
        }

        if ($parameters->categoryID !== 0 && !self::isValidCategory($parameters->categoryID)) {
            return FileProcessorPreflightResult::InvalidContext;
        }

        $allowedFileExtensions = $this->getAllowedFileExtensions($context);
        if (
            !\in_array('*', $allowedFileExtensions, true)
            && !FileUtil::endsWithAllowedExtension($filename, $allowedFileExtensions)
        ) {
            return FileProcessorPreflightResult::FileExtensionNotPermitted;
        }

        return FileProcessorPreflightResult::Passed;
    }

    #[\Override]
    public function canAdopt(File $file, array $context): bool
    {
        $parameters = $this->mapContext($context);
        if ($parameters === null) {
            return false;
        }

        $media = Media::findByFileID($file->fileID);
        if ($media === null) {
            return true;
        }

        return $media->mediaID === $parameters->mediaID;
    }

    #[\Override]
    public function adopt(File $file, array $context): void
    {
        $parameters = $this->mapContext($context);
        if ($parameters === null) {
            return;
        }

        if ($parameters->mediaID !== 0) {
            // The replacement is assigned when the form is saved.
            return;
        }

        if ($parameters->imagesOnly && !$file->isImage()) {
            // The file is not adopted and will be removed eventually.
            return;
        }

        $categoryID = null;
        if ($parameters->categoryID !== 0 && self::isValidCategory($parameters->categoryID)) {
            $categoryID = $parameters->categoryID;
        }

        new CreateMedia(
            MediaBuilder::forCreate()
                ->setFile($file)
                ->setCategoryID($categoryID)
                ->setUser(WCF::getUser()->userID ?: null, WCF::getUser()->username)
                ->setUploadTime(\TIME_NOW)
        )();
    }

    #[\Override]
    public function canDelete(File $file): bool
    {
        return Media::findByFileID($file->fileID)?->canManage() ?? false;
    }

    #[\Override]
    public function canDownload(File $file): bool
    {
        return Media::findByFileID($file->fileID)?->isAccessible() ?? false;
    }

    #[\Override]
    public function getMaximumCount(array $context): ?int
    {
        $parameters = $this->mapContext($context);
        if ($parameters === null) {
            return 0;
        }

        if ($parameters->mediaID !== 0) {
            return 1;
        }

        return null;
    }

    #[\Override]
    public function getThumbnailFormats(): array
    {
        $formats = [];
        foreach (Media::getThumbnailSizes() as $identifier => $size) {
            $formats[] = new ThumbnailFormat(
                $identifier,
                $size['height'],
                $size['width'],
                (bool)$size['retainDimensions'],
            );
        }

        return $formats;
    }

    #[\Override]
    public function getUploadResponse(File $file): array
    {
        $media = Media::findByFileID($file->fileID);
        if ($media === null) {
            return [];
        }

        return [
            'mediaID' => $media->mediaID,
        ];
    }

    #[\Override]
    public function trackDownload(File $file): void
    {
        $media = Media::findByFileID($file->fileID);
        if ($media === null) {
            return;
        }

        MediaBuilder::forUpdate($media)
            ->trackDownload(\TIME_NOW)
            ->update();
    }

    #[\Override]
    public function sourceFilenameChanged(File $file): void
    {
        $media = Media::findByFileID($file->fileID);
        if ($media === null) {
            return;
        }

        // The conversion of the image format changes the denormalized meta data.
        MediaBuilder::forUpdate($media)
            ->setFile($file)
            ->update();
    }

    #[\Override]
    public function delete(array $fileIDs, array $thumbnailIDs): void
    {
        $mediaList = new MediaList();
        $mediaList->getConditionBuilder()->add('media.fileID IN (?)', [$fileIDs]);
        $mediaList->readObjects();

        if ($mediaList->count() === 0) {
            return;
        }

        // The files are being deleted by the caller.
        MediaBuilder::deleteAll($mediaList->getObjectIDs());

        EventHandler::getInstance()->fire(new MediaDeleted($mediaList->getObjects()));
    }

    /**
     * Returns the html element to upload new media files.
     *
     * @since 6.3
     */
    public function toHtmlElement(int $categoryID = 0, bool $imagesOnly = false): string
    {
        return FileProcessor::getInstance()->getHtmlElement($this, [
            'categoryID' => $categoryID,
            'imagesOnly' => (int)$imagesOnly,
        ]);
    }

    private static function isValidCategory(int $categoryID): bool
    {
        $category = CategoryHandler::getInstance()->getCategory($categoryID);

        return $category !== null && $category->getObjectType()->objectType === 'com.woltlab.wcf.media.category';
    }

    private function getMediaFromContext(MediaFileProcessorContext $parameters): ?Media
    {
        if ($parameters->mediaID === 0) {
            return null;
        }

        return MediaRuntimeCache::getInstance()->getObject($parameters->mediaID);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function mapContext(array $context): ?MediaFileProcessorContext
    {
        try {
            $parameters = Helper::mapQueryParameters($context, MediaFileProcessorContext::class);
            \assert($parameters instanceof MediaFileProcessorContext);
        } catch (MappingError) {
            return null;
        }

        return $parameters;
    }
}

/** @internal */
final class MediaFileProcessorContext
{
    public function __construct(
        /** @var non-negative-int */
        public readonly int $categoryID = 0,

        public readonly bool $imagesOnly = false,

        /** @var non-negative-int */
        public readonly int $mediaID = 0,
    ) {}
}
