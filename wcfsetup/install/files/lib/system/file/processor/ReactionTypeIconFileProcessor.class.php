<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\data\reaction\type\ReactionTypeEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\ImageUtil;

/**
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ReactionTypeIconFileProcessor extends AbstractFileProcessor
{
    #[\Override]
    public function getObjectTypeName(): string
    {
        return 'com.woltlab.wcf.reactionType.icon';
    }

    #[\Override]
    public function getAllowedFileExtensions(array $context): array
    {
        return [
            'gif',
            'jpg',
            'jpeg',
            'png',
            'svg',
            'webp',
        ];
    }

    #[\Override]
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult
    {
        if (!WCF::getSession()->hasPermission('admin.content.reaction.canManageReactionType')) {
            return FileProcessorPreflightResult::InsufficientPermissions;
        }

        if (!FileUtil::endsWithAllowedExtension($filename, $this->getAllowedFileExtensions($context))) {
            return FileProcessorPreflightResult::FileExtensionNotPermitted;
        }

        return FileProcessorPreflightResult::Passed;
    }

    #[\Override]
    public function validateUpload(File $file): void
    {
        if (!ImageUtil::isImageMimeType($file->mimeType) && $file->mimeType !== 'image/svg+xml') {
            throw new UserInputException('file', 'noImage');
        }
    }

    #[\Override]
    public function canAdopt(File $file, array $context): bool
    {
        $reactionTypeID = $this->getReactionTypeIDByFile($file);
        if ($reactionTypeID === null) {
            return true;
        }

        return $reactionTypeID === ($context['objectID'] ?? null);
    }

    #[\Override]
    public function adopt(File $file, array $context): void
    {
        // The file is assigned when the form is saved.
    }

    #[\Override]
    public function canDelete(File $file): bool
    {
        return WCF::getSession()->hasPermission('admin.content.reaction.canManageReactionType');
    }

    #[\Override]
    public function canDownload(File $file): bool
    {
        return true;
    }

    #[\Override]
    public function delete(array $fileIDs, array $thumbnailIDs): void
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('iconFileID IN (?)', [$fileIDs]);

        $sql = "UPDATE  wcf1_reaction_type
                SET     iconFileID = ?
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([null, ...$conditionBuilder->getParameters()]);

        if ($statement->getAffectedRows() > 0) {
            ReactionTypeEditor::resetCache();
        }
    }

    #[\Override]
    public function sourceFilenameChanged(File $file): void
    {
        // The reaction type cache holds the file and therefore its pathname.
        if ($this->getReactionTypeIDByFile($file) !== null) {
            ReactionTypeEditor::resetCache();
        }
    }

    #[\Override]
    public function isSingleFile(): bool
    {
        return true;
    }

    #[\Override]
    public function serveSvgStatically(): bool
    {
        // Uploads are restricted to administrators through `acceptUpload()`.
        return true;
    }

    private function getReactionTypeIDByFile(File $file): ?int
    {
        $sql = "SELECT  reactionTypeID
                FROM    wcf1_reaction_type
                WHERE   iconFileID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$file->fileID]);
        $reactionTypeID = $statement->fetchSingleColumn();

        return $reactionTypeID === false ? null : $reactionTypeID;
    }
}
