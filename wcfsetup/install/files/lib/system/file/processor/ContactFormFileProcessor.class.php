<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\system\WCF;

/**
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ContactFormFileProcessor extends AbstractFileProcessor
{
    private const SESSION_VARIABLE = 'contact_form_file_processor_%d';

    #[\Override]
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult
    {
        if (\CONTACT_FORM_ENABLE_ATTACHMENTS === 0) {
            return FileProcessorPreflightResult::InsufficientPermissions;
        }

        return FileProcessorPreflightResult::Passed;
    }

    #[\Override]
    public function canAdopt(File $file, array $context): bool
    {
        return true;
    }

    #[\Override]
    public function adopt(File $file, array $context): void
    {
        // Save the `fileID` in the session variable so that the current user can download or delete it.
        WCF::getSession()->register(\sprintf(self::SESSION_VARIABLE, $file->fileID), \TIME_NOW);
        WCF::getSession()->update();
    }

    #[\Override]
    public function getMaximumCount(array $context): ?int
    {
        return WCF::getSession()->getPermission('user.contactForm.attachment.maxCount');
    }

    #[\Override]
    public function getAllowedFileExtensions(array $context): array
    {
        return \explode("\n", WCF::getSession()->getPermission('user.contactForm.attachment.allowedExtensions'));
    }

    #[\Override]
    public function getMaximumSize(array $context): ?int
    {
        return WCF::getSession()->getPermission('user.contactForm.attachment.maxSize');
    }

    #[\Override]
    public function canDelete(File $file): bool
    {
        return WCF::getSession()->getVar(
            \sprintf(self::SESSION_VARIABLE, $file->fileID)
        ) !== null;
    }

    #[\Override]
    public function canDownload(File $file): bool
    {
        if (WCF::getSession()->hasPermission('admin.contact.canManageContactForm')) {
            return true;
        }

        return WCF::getSession()->getVar(
            \sprintf(self::SESSION_VARIABLE, $file->fileID)
        ) !== null;
    }

    #[\Override]
    public function delete(array $fileIDs, array $thumbnailIDs): void {}

    #[\Override]
    public function getObjectTypeName(): string
    {
        return 'com.woltlab.wcf.contact.form';
    }
}
