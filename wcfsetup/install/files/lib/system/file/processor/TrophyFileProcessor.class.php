<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * File processor for trophy images.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class TrophyFileProcessor extends AbstractFileProcessor
{
    public const IMAGE_MIN_SIZE = 64;

    public const IMAGE_MAX_SIZE = 128;

    #[\Override]
    public function getObjectTypeName(): string
    {
        return 'com.woltlab.wcf.trophy';
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
    public function canAdopt(File $file, array $context): bool
    {
        $trophyFromContext = $this->getTrophy($context);
        $trophyFromCoreFile = $this->getTrophyByFile($file);

        if ($trophyFromCoreFile === null) {
            return true;
        }

        if ($trophyFromContext->trophyID === $trophyFromCoreFile->trophyID) {
            return true;
        }

        return false;
    }

    #[\Override]
    public function adopt(File $file, array $context): void
    {
        $trophy = $this->getTrophy($context);
        if ($trophy === null) {
            return;
        }

        (new TrophyEditor($trophy))->update([
            'imageFileID' => $file->fileID,
        ]);
    }

    #[\Override]
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult
    {
        if (!WCF::getSession()->getPermission('admin.trophy.canManageTrophy')) {
            return FileProcessorPreflightResult::InsufficientPermissions;
        }

        if (isset($context['objectID'])) {
            $trophy = $this->getTrophy($context);
            if ($trophy === null) {
                return FileProcessorPreflightResult::InvalidContext;
            }
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

        if (
            $imageData[0] != self::IMAGE_MIN_SIZE
            && $imageData[0] != self::IMAGE_MAX_SIZE
        ) {
            throw new UserInputException('file', 'wrongSize');
        }
    }

    #[\Override]
    public function canDelete(File $file): bool
    {
        return WCF::getSession()->getPermission('admin.trophy.canManageTrophy');
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
        $conditionBuilder->add('imageFileID IN (?)', [$fileIDs]);

        $sql = "UPDATE wcf1_trophy
                SET    imageFileID = ?
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([null, ...$conditionBuilder->getParameters()]);
    }

    #[\Override]
    public function countExistingFiles(array $context): int
    {
        return $this->getTrophy($context)?->imageFileID === null ? 0 : 1;
    }

    #[\Override]
    public function getImageCropperConfiguration(): ImageCropperConfiguration
    {
        return ImageCropperConfiguration::forMinMax(
            new ImageCropSize(self::IMAGE_MIN_SIZE, self::IMAGE_MIN_SIZE),
            new ImageCropSize(self::IMAGE_MAX_SIZE, self::IMAGE_MAX_SIZE),
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    private function getTrophy(array $context): ?Trophy
    {
        $trophyID = $context['objectID'] ?? null;
        if ($trophyID === null) {
            return null;
        }

        return new Trophy($trophyID);
    }

    private function getTrophyByFile(File $file): ?Trophy
    {
        $sql = "SELECT *
                FROM   wcf1_trophy
                WHERE  imageFileID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$file->fileID]);

        return $statement->fetchObject(Trophy::class);
    }
}
