<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\system\database\util\PreparedStatementConditionBuilder;
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
    private const SESSION_VARIABLE_PREFIX = 'contact_form_file_processor_';
    private const SESSION_VARIABLE = self::SESSION_VARIABLE_PREFIX . '%d';

    #[\Override]
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult
    {
        if (!\CONTACT_FORM_ENABLE_ATTACHMENTS) {
            return FileProcessorPreflightResult::InsufficientPermissions;
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
        return true;
    }

    #[\Override]
    public function adopt(File $file, array $context): void
    {
        // Save the `fileID` in the session variable so that the current user can download or delete it.
        WCF::getSession()->register(\sprintf(self::SESSION_VARIABLE, $file->fileID), TIME_NOW);
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
        return WCF::getSession()->getVar(
            \sprintf(self::SESSION_VARIABLE, $file->fileID)
        ) !== null;
    }

    #[\Override]
    public function canDownload(File $file): bool
    {
        if (WCF::getSession()->getPermission('admin.contact.canManageContactForm')) {
            return true;
        }

        return WCF::getSession()->getVar(
            \sprintf(self::SESSION_VARIABLE, $file->fileID)
        ) !== null;
    }

    #[\Override]
    public function countExistingFiles(array $context): int
    {
        $fileIDs = $this->getFileIDsFromSession();
        if ($fileIDs === []) {
            return 0;
        }

        $objectTypeID = FileProcessor::getInstance()->getObjectType($this->getObjectTypeName())?->objectTypeID;
        if ($objectTypeID === null) {
            return 0;
        }

        // The session may reference files that have already been deleted or
        // that were pruned by `FileCleanUpCronjob`, those must not count
        // towards the limit.
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('fileID IN (?)', [$fileIDs]);
        $conditionBuilder->add('objectTypeID = ?', [$objectTypeID]);

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_file
                {$conditionBuilder}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        return (int)$statement->fetchSingleColumn();
    }

    #[\Override]
    public function delete(array $fileIDs, array $thumbnailIDs): void
    {
        $this->unregisterFiles($fileIDs);
    }

    #[\Override]
    public function getObjectTypeName(): string
    {
        return 'com.woltlab.wcf.contact.form';
    }

    /**
     * Releases the claim of the current user on the files of a message that
     * has been submitted. The files are retained until they are pruned, but
     * they must not count towards the upload limit of any future message.
     *
     * @param list<int> $fileIDs
     */
    public function releaseFiles(array $fileIDs): void
    {
        $this->unregisterFiles($fileIDs);

        // `WCF::destruct()` writes the session only after the response has been
        // flushed, therefore the redirect could otherwise race the removal.
        WCF::getSession()->update();
    }

    /**
     * @param list<int> $fileIDs
     */
    private function unregisterFiles(array $fileIDs): void
    {
        foreach ($fileIDs as $fileID) {
            WCF::getSession()->unregister(
                \sprintf(self::SESSION_VARIABLE, $fileID)
            );
        }
    }

    /**
     * @return list<int>
     */
    private function getFileIDsFromSession(): array
    {
        $fileIDs = [];
        foreach (\array_keys(WCF::getSession()->getVariables()) as $key) {
            $key = (string)$key;
            if (!\str_starts_with($key, self::SESSION_VARIABLE_PREFIX)) {
                continue;
            }

            $fileIDs[] = (int)\substr($key, \strlen(self::SESSION_VARIABLE_PREFIX));
        }

        return $fileIDs;
    }
}
