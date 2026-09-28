<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\data\user\rank\UserRankEditor;
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
final class UserRankImageFileProcessor extends AbstractFileProcessor
{
    #[\Override]
    public function getObjectTypeName(): string
    {
        return 'com.woltlab.wcf.user.rank.image';
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
        if (!WCF::getSession()->hasPermission('admin.user.rank.canManageRank')) {
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
        $rankID = $this->getRankIDByFile($file);
        if ($rankID === null) {
            return true;
        }

        return $rankID === ($context['objectID'] ?? null);
    }

    #[\Override]
    public function adopt(File $file, array $context): void
    {
        // The file is assigned when the form is saved.
    }

    #[\Override]
    public function canDelete(File $file): bool
    {
        return WCF::getSession()->hasPermission('admin.user.rank.canManageRank');
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
        $conditionBuilder->add('rankImageFileID IN (?)', [$fileIDs]);

        $sql = "UPDATE  wcf1_user_rank
                SET     rankImageFileID = ?
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([null, ...$conditionBuilder->getParameters()]);

        if ($statement->getAffectedRows() > 0) {
            UserRankEditor::resetCache();
        }
    }

    #[\Override]
    public function sourceFilenameChanged(File $file): void
    {
        // The rank cache holds the file and therefore its pathname.
        if ($this->getRankIDByFile($file) !== null) {
            UserRankEditor::resetCache();
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

    private function getRankIDByFile(File $file): ?int
    {
        $sql = "SELECT  rankID
                FROM    wcf1_user_rank
                WHERE   rankImageFileID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$file->fileID]);
        $rankID = $statement->fetchSingleColumn();

        return $rankID === false ? null : $rankID;
    }
}
