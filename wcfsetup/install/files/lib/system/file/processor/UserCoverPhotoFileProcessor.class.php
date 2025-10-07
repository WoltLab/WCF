<?php

namespace wcf\system\file\processor;

use wcf\data\file\File;
use wcf\data\user\cover\photo\UserCoverPhoto;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\command\user\SetCoverPhoto;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserCoverPhotoFileProcessor extends AbstractFileProcessor
{
    private const SESSION_VARIABLE = 'wcf_user_cover_photo_processor_%d';
    public const SMALL_THUMBNAIL_HEIGHT = 200;
    public const SMALL_THUMBNAIL_WIDTH = 800;

    #[\Override]
    public function getObjectTypeName(): string
    {
        return 'com.woltlab.wcf.user.coverPhoto';
    }

    #[\Override]
    public function canAdopt(File $file, array $context): bool
    {
        $userFromContext = $this->getUser($context);
        $userFromCoreFile = $this->getUserByFile($file);

        if ($userFromCoreFile === null) {
            return true;
        }

        if ($userFromContext->userID === $userFromCoreFile->userID) {
            return true;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function getUser(array $context): ?UserProfile
    {
        $userID = $context['objectID'] ?? null;
        if ($userID === null) {
            return null;
        }

        return UserProfileRuntimeCache::getInstance()->getObject($userID);
    }

    private function getUserByFile(File $file): ?UserProfile
    {
        $sql = "SELECT userID
                FROM   wcf1_user
                WHERE  coverPhotoFileID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$file->fileID]);
        $userID = $statement->fetchSingleColumn();

        if ($userID === false) {
            return null;
        }

        return UserProfileRuntimeCache::getInstance()->getObject($userID);
    }

    #[\Override]
    public function adopt(File $file, array $context): void
    {
        $user = $this->getUser($context);
        if ($user === null) {
            return;
        }

        // Save the `fileID` in the session variable so that the current user can delete the old cover photo
        if ($user->coverPhotoFileID !== null) {
            WCF::getSession()->register(\sprintf(self::SESSION_VARIABLE, $user->coverPhotoFileID), TIME_NOW);
            WCF::getSession()->update();
        }

        (new SetCoverPhoto($user->getDecoratedObject(), $file))();
    }

    #[\Override]
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult
    {
        $user = $this->getUser($context);

        if ($user === null) {
            return FileProcessorPreflightResult::InvalidContext;
        }

        if (!$user->canEditCoverPhoto()) {
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
    public function getMaximumSize(array $context): ?int
    {
        return WCF::getSession()->getPermission('user.profile.coverPhoto.maxSize');
    }

    #[\Override]
    public function getAllowedFileExtensions(array $context): array
    {
        return [
            'gif',
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];
    }

    #[\Override]
    public function validateUpload(File $file): void
    {
        $imageData = @\getimagesize($file->getPathname());
        if ($imageData === false) {
            throw new UserInputException('file', 'noImage');
        }

        [$width, $height] = $imageData;

        if (
            $width < UserCoverPhoto::MIN_WIDTH ||
            $width > UserCoverPhoto::MAX_WIDTH ||
            $height < UserCoverPhoto::MIN_HEIGHT ||
            $height > UserCoverPhoto::MAX_HEIGHT
        ) {
            throw new UserInputException('file', 'wrongSize');
        }
    }

    #[\Override]
    public function canDelete(File $file): bool
    {
        $user = $this->getUserByFile($file);
        if ($user === null) {
            return WCF::getSession()->getVar(
                \sprintf(self::SESSION_VARIABLE, $file->fileID)
            ) !== null;
        }

        return $user->canEditCoverPhoto();
    }

    #[\Override]
    public function canDownload(File $file): bool
    {
        $user = $this->getUserByFile($file);
        if ($user === null) {
            return false;
        }

        return $user->canSeeCoverPhoto();
    }

    #[\Override]
    public function delete(array $fileIDs, array $thumbnailIDs): void
    {
        \array_map(
            static fn(int $fileID) => WCF::getSession()->unregister(
                \sprintf(self::SESSION_VARIABLE, $fileID)
            ),
            $fileIDs
        );

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('coverPhotoFileID IN (?)', [$fileIDs]);

        $sql = "UPDATE wcf1_user
                SET    coverPhotoFileID = ?
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([null, ...$conditionBuilder->getParameters()]);
    }

    #[\Override]
    public function countExistingFiles(array $context): ?int
    {
        $user = $this->getUser($context);
        if ($user === null) {
            return null;
        }

        return $user->coverPhotoFileID === null ? 0 : 1;
    }

    #[\Override]
    public function getImageCropperConfiguration(): ImageCropperConfiguration
    {
        return ImageCropperConfiguration::forMinMax(
            new ImageCropSize(UserCoverPhoto::MIN_WIDTH, UserCoverPhoto::MIN_HEIGHT),
            new ImageCropSize(UserCoverPhoto::MAX_WIDTH, UserCoverPhoto::MAX_HEIGHT)
        );
    }

    #[\Override]
    public function getThumbnailFormats(): array
    {
        return [
            new ThumbnailFormat(
                'small',
                self::SMALL_THUMBNAIL_HEIGHT,
                self::SMALL_THUMBNAIL_WIDTH,
                true,
            ),
        ];
    }
}
