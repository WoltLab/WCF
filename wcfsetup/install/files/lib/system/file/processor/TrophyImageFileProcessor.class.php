<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\data\trophy\TrophyBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * File processor for the images of image trophies.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class TrophyImageFileProcessor extends AbstractFileProcessor
{
    /**
     * Minimum width and height of the image.
     */
    public const MINIMUM_SIZE = 64;

    /**
     * Images that are larger than this are downscaled before the upload.
     */
    public const MAXIMUM_SIZE = 128;

    #[\Override]
    public function getObjectTypeName(): string
    {
        return 'com.woltlab.wcf.trophy.image';
    }

    #[\Override]
    public function getAllowedFileExtensions(array $context): array
    {
        return [
            'gif',
            'jpg',
            'jpeg',
            'png',
            'webp',
        ];
    }

    #[\Override]
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult
    {
        if (!WCF::getSession()->hasPermission('admin.trophy.canManageTrophy')) {
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
        $imageData = @\getimagesize($file->getPathname());
        if ($imageData === false) {
            throw new UserInputException('file', 'noImage');
        }

        if ($imageData[0] !== $imageData[1]) {
            throw new UserInputException('file', 'notSquare');
        }

        if ($imageData[0] < self::MINIMUM_SIZE) {
            throw new UserInputException('file', 'tooSmall');
        }
    }

    #[\Override]
    public function getResizeConfiguration(): ResizeConfiguration
    {
        return new ResizeConfiguration(
            self::MAXIMUM_SIZE,
            self::MAXIMUM_SIZE,
            ResizeFileType::Keep,
            100
        );
    }

    #[\Override]
    public function canAdopt(File $file, array $context): bool
    {
        $trophyID = $this->getTrophyIDByFile($file);
        if ($trophyID === null) {
            return true;
        }

        return $trophyID === ($context['objectID'] ?? null);
    }

    #[\Override]
    public function adopt(File $file, array $context): void
    {
        // The file is assigned when the form is saved.
    }

    #[\Override]
    public function canDelete(File $file): bool
    {
        return WCF::getSession()->hasPermission('admin.trophy.canManageTrophy');
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

        $sql = "UPDATE  wcf1_trophy
                SET     iconFileID = ?
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([null, ...$conditionBuilder->getParameters()]);

        if ($statement->getAffectedRows() > 0) {
            TrophyBuilder::resetCache();
        }
    }

    #[\Override]
    public function sourceFilenameChanged(File $file): void
    {
        // The trophy cache holds the file and therefore its pathname.
        if ($this->getTrophyIDByFile($file) !== null) {
            TrophyBuilder::resetCache();
        }
    }

    #[\Override]
    public function isSingleFile(): bool
    {
        return true;
    }

    private function getTrophyIDByFile(File $file): ?int
    {
        $sql = "SELECT  trophyID
                FROM    wcf1_trophy
                WHERE   iconFileID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$file->fileID]);
        $trophyID = $statement->fetchSingleColumn();

        return $trophyID === false ? null : $trophyID;
    }
}
