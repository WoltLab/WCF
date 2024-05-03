<?php

namespace wcf\system\file\processor;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentAction;
use wcf\data\attachment\AttachmentEditor;
use wcf\data\file\File;
use wcf\data\file\thumbnail\FileThumbnail;
use wcf\http\Helper;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\file\processor\exception\UnexpectedThumbnailIdentifier;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class AttachmentFileProcessor implements IFileProcessor
{
    #[\Override]
    public function getTypeName(): string
    {
        return 'com.woltlab.wcf.attachment';
    }

    #[\Override]
    public function getAllowedFileExtensions(array $context): array
    {
        $attachmentHandler = $this->getAttachmentHandlerFromContext($context);
        if ($attachmentHandler === null) {
            return [];
        }

        return $attachmentHandler->getAllowedExtensions();
    }

    #[\Override]
    public function adopt(File $file, array $context): void
    {
        $attachmentHandler = $this->getAttachmentHandlerFromContext($context);
        if ($attachmentHandler === null) {
            // This can only happen whent he associated object has vanished
            // while the file was being processed. There is nothing we can
            // meaningfully do here.
            return;
        }

        AttachmentEditor::fastCreate([
            'objectTypeID' => $attachmentHandler->getObjectType()->objectTypeID,
            'objectID' => $attachmentHandler->getObjectID(),
            'tmpHash' => $attachmentHandler->getTmpHashes()[0] ?? '',
            'fileID' => $file->fileID,
            'userID' => WCF::getUser()->userID ?: null,
        ]);
    }

    #[\Override]
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult
    {
        $attachmentHandler = $this->getAttachmentHandlerFromContext($context);
        if ($attachmentHandler === null) {
            return FileProcessorPreflightResult::InvalidContext;
        }

        if (!$attachmentHandler->canUpload()) {
            return FileProcessorPreflightResult::InsufficientPermissions;
        }

        if ($fileSize > $attachmentHandler->getMaxSize()) {
            return FileProcessorPreflightResult::FileSizeTooLarge;
        }

        if (!FileUtil::endsWithAllowedExtension($filename, $attachmentHandler->getAllowedExtensions())) {
            return FileProcessorPreflightResult::FileExtensionNotPermitted;
        }

        return FileProcessorPreflightResult::Passed;
    }

    #[\Override]
    public function canDelete(File $file): bool
    {
        $attachment = Attachment::findByFileID($file->fileID);
        if ($attachment === null) {
            return false;
        }

        return $attachment->canDelete();
    }

    #[\Override]
    public function canDownload(File $file): bool
    {
        $attachment = Attachment::findByFileID($file->fileID);
        if ($attachment === null) {
            return false;
        }

        return $attachment->canDownload();
    }

    #[\Override]
    public function getUploadResponse(File $file): array
    {
        $attachment = Attachment::findByFileID($file->fileID);
        if ($attachment === null) {
            return [];
        }

        return [
            'attachmentID' => $attachment->attachmentID,
        ];
    }

    public function toHtmlElement(string $objectType, int $objectID, string $tmpHash, int $parentObjectID): string
    {
        return FileProcessor::getInstance()->getHtmlElement(
            $this,
            [
                'objectType' => $objectType,
                'objectID' => $objectID,
                'parentObjectID' => $parentObjectID,
                'tmpHash' => $tmpHash,
            ],
        );
    }

    #[\Override]
    public function getResizeConfiguration(): ResizeConfiguration
    {
        if (!\ATTACHMENT_IMAGE_AUTOSCALE) {
            return ResizeConfiguration::unbounded();
        }

        return new ResizeConfiguration(
            \ATTACHMENT_IMAGE_AUTOSCALE_MAX_WIDTH,
            \ATTACHMENT_IMAGE_AUTOSCALE_MAX_HEIGHT,
            ResizeFileType::fromString(\ATTACHMENT_IMAGE_AUTOSCALE_FILE_TYPE),
            \ATTACHMENT_IMAGE_AUTOSCALE_QUALITY
        );
    }

    #[\Override]
    public function getThumbnailFormats(): array
    {
        return [
            new ThumbnailFormat(
                '',
                \ATTACHMENT_THUMBNAIL_HEIGHT,
                \ATTACHMENT_THUMBNAIL_WIDTH,
                !!\ATTACHMENT_RETAIN_DIMENSIONS,
            ),
            new ThumbnailFormat(
                'tiny',
                144,
                144,
                false,
            ),
        ];
    }

    #[\Override]
    public function adoptThumbnail(FileThumbnail $thumbnail): void
    {
        $attachment = Attachment::findByFileID($thumbnail->fileID);
        if ($attachment === null) {
            // The associated attachment (or file) has vanished while the
            // thumbnail was being created. There is nothing to do here, it will
            // cleaned up eventually.
            return;
        }

        $columnName = match ($thumbnail->identifier) {
            '' => 'thumbnailID',
            'tiny' => 'tinyThumbnailID',
            'default' => throw new UnexpectedThumbnailIdentifier($thumbnail->identifier),
        };

        $attachmentEditor = new AttachmentEditor($attachment);
        $attachmentEditor->update([
            $columnName => $thumbnail->thumbnailID,
        ]);
    }

    #[\Override]
    public function delete(array $fileIDs, array $thumbnailIDs): void
    {
        (new AttachmentAction($fileIDs, 'delete'))->executeAction();
    }

    private function getAttachmentHandlerFromContext(array $context): ?AttachmentHandler
    {
        try {
            $parameters = Helper::mapQueryParameters($context, AttachmentFileProcessorContext::class);
        } catch (MappingError) {
            return null;
        }

        \assert($parameters instanceof AttachmentFileProcessorContext);

        return new AttachmentHandler(
            $parameters->objectType,
            $parameters->objectID,
            $parameters->tmpHash,
            $parameters->parentObjectID,
        );
    }
}

/** @internal */
final class AttachmentFileProcessorContext
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $objectType,

        /** @var non-negative-int */
        public readonly int $objectID,

        /** @var non-negative-int */
        public readonly int $parentObjectID,

        public readonly string $tmpHash,
    ) {
    }
}
