<?php

namespace wcf\system\file\processor;

use wcf\data\attachment\AttachmentEditor;
use wcf\data\file\File;
use wcf\system\attachment\AttachmentHandler;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class AttachmentFileProcessor implements IFileProcessor
{
    public function getTypeName(): string
    {
        return 'com.woltlab.wcf.attachment';
    }

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
}
