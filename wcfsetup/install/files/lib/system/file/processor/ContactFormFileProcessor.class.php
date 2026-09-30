<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\FileUtil;

/**
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ContactFormFileProcessor extends AbstractFileProcessor
{
    #[\Override]
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult
    {
        if (\CONTACT_FORM_ENABLE_ATTACHMENTS === 0) {
            return FileProcessorPreflightResult::InsufficientPermissions;
        }

        if (UploaderToken::fromContext($context) === null) {
            return FileProcessorPreflightResult::InvalidContext;
        }

        if ($fileSize > $this->getMaximumSize($context)) {
            return FileProcessorPreflightResult::FileSizeTooLarge;
        }

        if (!FileUtil::endsWithAllowedExtension($filename, $this->getAllowedFileExtensions($context))) {
            return FileProcessorPreflightResult::FileExtensionNotPermitted;
        }

        return FileProcessorPreflightResult::Passed;
    }

    #[\Override]
    public function canAdopt(File $file, array $context): bool
    {
        $uploaderToken = UploaderToken::fromContext($context);
        if ($uploaderToken === null) {
            return false;
        }

        // The file is claimed by `adopt()`, which is invoked right after this check
        // once the upload has been completed.
        if (UploaderToken::isUnclaimed($file)) {
            return true;
        }

        return UploaderToken::matches($file, $uploaderToken);
    }

    #[\Override]
    public function adopt(File $file, array $context): void
    {
        $uploaderToken = UploaderToken::fromContext($context);
        if ($uploaderToken !== null) {
            UploaderToken::store($file, $uploaderToken);
        }
    }

    #[\Override]
    public function usesUploaderToken(): bool
    {
        return true;
    }

    #[\Override]
    public function getMaximumCount(array $context): ?int
    {
        return WCF::getSession()->getPermission('user.contactForm.attachment.maxCount');
    }

    #[\Override]
    public function getAllowedFileExtensions(array $context): array
    {
        // An untrimmed list yields extensions with a trailing `\r` that can
        // never match, plus an empty element for the trailing newline that
        // would make the extension check match anything.
        $extensions = ArrayUtil::trim(
            \explode("\n", WCF::getSession()->getPermission('user.contactForm.attachment.allowedExtensions'))
        );
        \assert(\is_array($extensions));

        return \array_values($extensions);
    }

    #[\Override]
    public function getMaximumSize(array $context): ?int
    {
        return WCF::getSession()->getPermission('user.contactForm.attachment.maxSize');
    }

    #[\Override]
    public function canDelete(File $file): bool
    {
        // Only the uploader can delete a file, identified by their token.
        return false;
    }

    #[\Override]
    public function canDeleteWithUploaderToken(File $file, string $uploaderToken): bool
    {
        return UploaderToken::matches($file, $uploaderToken);
    }

    #[\Override]
    public function canDownload(File $file): bool
    {
        return WCF::getSession()->hasPermission('admin.contact.canManageContactForm');
    }

    #[\Override]
    public function canDownloadWithUploaderToken(File $file, string $uploaderToken): bool
    {
        if ($this->canDownload($file)) {
            return true;
        }

        return UploaderToken::matches($file, $uploaderToken);
    }

    #[\Override]
    public function countExistingFiles(array $context): int
    {
        $uploaderToken = UploaderToken::fromContext($context);
        if ($uploaderToken === null) {
            return 0;
        }

        return UploaderToken::countFiles($uploaderToken);
    }

    #[\Override]
    public function delete(array $fileIDs, array $thumbnailIDs): void
    {
        // The records of the uploader tokens are deleted along with the files.
    }

    #[\Override]
    public function getObjectTypeName(): string
    {
        return 'com.woltlab.wcf.contact.form';
    }

    /**
     * Releases the claim of the uploader on the files of a message that has been
     * submitted. The files are retained until they are pruned, but they can no
     * longer be deleted by the uploader or be adopted by another message.
     *
     * @param list<int> $fileIDs
     */
    public function releaseFiles(array $fileIDs): void
    {
        UploaderToken::release($fileIDs);
    }
}
