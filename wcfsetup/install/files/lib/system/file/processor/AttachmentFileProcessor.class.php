<?php

namespace wcf\system\file\processor;

use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentEditor;
use wcf\data\file\File;
use wcf\data\file\thumbnail\FileThumbnail;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\exception\NotImplementedException;

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
        // TODO: Properly validate the shape of `$context`.
        $objectType = $context['objectType'] ?? '';
        $objectID = \intval($context['objectID'] ?? 0);
        $parentObjectID = \intval($context['parentObjectID'] ?? 0);
        $tmpHash = $context['tmpHash'] ?? '';

        $attachmentHandler = new AttachmentHandler($objectType, $objectID, $tmpHash, $parentObjectID);

        return $attachmentHandler->getAllowedExtensions();
    }

    #[\Override]
    public function adopt(File $file, array $context): void
    {
        // TODO: Properly validate the shape of `$context`.
        $objectType = $context['objectType'] ?? '';
        $objectID = \intval($context['objectID'] ?? 0);
        $parentObjectID = \intval($context['parentObjectID'] ?? 0);
        $tmpHash = $context['tmpHash'] ?? '';

        $attachmentHandler = new AttachmentHandler($objectType, $objectID, $tmpHash, $parentObjectID);

        // TODO: How do we want to create the attachments? Do we really want to
        //       keep using the existing attachment table though?
        AttachmentEditor::fastCreate([
            'objectTypeID' => $attachmentHandler->getObjectType()->objectTypeID,
            'objectID' => $attachmentHandler->getObjectID(),
            'tmpHash' => $tmpHash,
            'fileID' => $file->fileID,
        ]);
    }

    #[\Override]
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult
    {
        // TODO: Properly validate the shape of `$context`.
        $objectType = $context['objectType'] ?? '';
        $objectID = \intval($context['objectID'] ?? 0);
        $parentObjectID = \intval($context['parentObjectID'] ?? 0);
        $tmpHash = $context['tmpHash'] ?? '';

        $attachmentHandler = new AttachmentHandler($objectType, $objectID, $tmpHash, $parentObjectID);
        if (!$attachmentHandler->canUpload()) {
            return FileProcessorPreflightResult::InsufficientPermissions;
        }

        if ($fileSize > $attachmentHandler->getMaxSize()) {
            return FileProcessorPreflightResult::FileSizeTooLarge;
        }

        // TODO: This is a typical use case and should be provided through a helper function.
        $extensions = \implode(
            "|",
            \array_map(
                static function (string $extension) {
                    $extension = \preg_quote($extension, '/');
                    $extension = \str_replace('\*', '.*', $extension);

                    return $extension;
                },
                $attachmentHandler->getAllowedExtensions()
            )
        );
        $extensionsPattern = '/(' . $extensions . ')$/i';
        if (!\preg_match($extensionsPattern, \mb_strtolower($filename))) {
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
            // TODO: How to handle this case?
            return;
        }

        $columnName = match ($thumbnail->identifier) {
            '' => 'thumbnailID',
            'tiny'=>'tinyThumbnailID',
            'default'=>throw new \RuntimeException('TODO'), // TODO
        };

        $attachmentEditor = new AttachmentEditor($attachment);
        $attachmentEditor->update([
            $columnName => $thumbnail->thumbnailID,
        ]);
    }
}
