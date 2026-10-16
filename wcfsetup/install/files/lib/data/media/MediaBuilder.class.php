<?php

namespace wcf\data\media;

use wcf\command\file\DeleteFiles;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\file\File;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\acl\simple\SimpleAclResolver;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;

/**
 * Builder for creating, updating and deleting media files.
 *
 * The localized title, caption and alternative text are stored in the
 * `wcf1_media_l10n` table (see `L10nStorage`).
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<Media>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 * @phpstan-type AclValues array{allowAll: bool, user: list<int>, group: list<int>}
 */
final class MediaBuilder extends DatabaseObjectBuilder
{
    private const ACL_OBJECT_TYPE = 'com.woltlab.wcf.media';

    /**
     * @var L10nValue
     */
    private array $title;

    /**
     * @var L10nValue
     */
    private array $caption;

    /**
     * @var L10nValue
     */
    private array $altText;

    /**
     * @var AclValues
     */
    private array $aclValues;

    public function setCategoryID(?int $categoryID): static
    {
        $this->properties['categoryID'] = $categoryID;

        return $this;
    }

    /**
     * Sets the user who uploaded the media file.
     */
    public function setUser(?int $userID, string $username): static
    {
        $this->properties['userID'] = $userID;
        $this->properties['username'] = $username;

        return $this;
    }

    public function setUploadTime(int $uploadTime): static
    {
        $this->properties['uploadTime'] = $uploadTime;

        return $this;
    }

    /**
     * Sets the time at which the physical file was replaced the last time.
     */
    public function setFileUpdateTime(int $fileUpdateTime): static
    {
        $this->properties['fileUpdateTime'] = $fileUpdateTime;

        return $this;
    }

    /**
     * Sets the physical file and the meta data that is denormalized for
     * sorting and filtering. A previous file is deleted when the media file
     * is updated.
     */
    public function setFile(File $file): static
    {
        $this->properties['fileID'] = $file->fileID;

        return $this->setFileData(
            $file->filename,
            $file->fileSize,
            $file->mimeType,
            $file->isImage(),
            $file->width ?? 0,
            $file->height ?? 0,
        );
    }

    /**
     * Sets the meta data of the physical file.
     */
    public function setFileData(
        string $filename,
        int $filesize,
        string $fileType,
        bool $isImage,
        int $width,
        int $height
    ): static {
        $this->properties['filename'] = $filename;
        $this->properties['filesize'] = $filesize;
        $this->properties['fileType'] = $fileType;
        $this->properties['isImage'] = (int)$isImage;
        $this->properties['width'] = $width;
        $this->properties['height'] = $height;

        return $this;
    }

    /**
     * Sets the hash of the physical file in the legacy storage.
     *
     * @deprecated 6.3 Only required for media files that have not been migrated to `wcf1_file` yet.
     */
    public function setFileHash(string $fileHash): static
    {
        $this->properties['fileHash'] = $fileHash;

        return $this;
    }

    /**
     * @param L10nValue $title
     */
    public function setTitle(array $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param L10nValue $caption
     */
    public function setCaption(array $caption): static
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * @param L10nValue $altText
     */
    public function setAltText(array $altText): static
    {
        $this->altText = $altText;

        return $this;
    }

    public function setCaptionEnableHtml(bool $captionEnableHtml): static
    {
        $this->properties['captionEnableHtml'] = (int)$captionEnableHtml;

        return $this;
    }

    /**
     * Sets the users and groups that can access the media file, see `SimpleAclHandler`.
     *
     * @param AclValues $aclValues
     */
    public function setAclValues(array $aclValues): static
    {
        $this->aclValues = $aclValues;

        return $this;
    }

    /**
     * Increases the number of downloads and updates the last download time.
     */
    public function trackDownload(int $time): static
    {
        $this->incrementProperties['downloads'] = 1;
        $this->properties['lastDownloadTime'] = $time;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['filename', 'uploadTime', 'username'];
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        $this->saveL10nValues($object);
        $this->saveAclValues($object);
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        if (isset($this->title) || isset($this->caption) || isset($this->altText)) {
            $this->saveL10nValues($object);
        }

        $this->saveAclValues($object);
        $this->deleteReplacedFile($object);
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        $objectTypeID = SimpleAclHandler::getInstance()->getObjectTypeID(self::ACL_OBJECT_TYPE);

        foreach (['wcf1_acl_simple_to_user', 'wcf1_acl_simple_to_group'] as $tableName) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('objectTypeID = ?', [$objectTypeID]);
            $conditionBuilder->add('objectID IN (?)', [$objectIDs]);

            $sql = "DELETE FROM {$tableName}
                    {$conditionBuilder}";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());
        }

        SimpleAclResolver::getInstance()->resetCache(self::ACL_OBJECT_TYPE);
    }

    private function saveL10nValues(Media $media): void
    {
        $values = [];
        foreach (['title', 'caption', 'altText'] as $columnName) {
            $value = $this->{$columnName} ?? null;
            if ($value === null && $this->isUpdate()) {
                $value = $this->getObject()->getL10nValues($columnName);
            }

            $values[$columnName] = $value ?: [L10nStorage::MONOLINGUAL => ''];
        }

        // `L10nStorage::setValues()` replaces all rows of the media file.
        (new L10nStorage(Media::getL10nDefinition()))->setValues($media->mediaID, $values);
    }

    /**
     * Deletes the previous physical file after it has been replaced. The file
     * is deleted only after the update, because deleting a file cannot be
     * rolled back.
     */
    private function deleteReplacedFile(Media $media): void
    {
        $oldMedia = $this->getObject();
        if (!\array_key_exists('fileID', $this->properties) || $oldMedia->fileID === $media->fileID) {
            return;
        }

        if ($oldMedia->fileID === null) {
            $oldMedia->deleteLegacyFiles();

            return;
        }

        $file = FileRuntimeCache::getInstance()->getObject($oldMedia->fileID);
        if ($file !== null) {
            new DeleteFiles([$file])();
        }
    }

    private function saveAclValues(Media $media): void
    {
        if (!isset($this->aclValues)) {
            return;
        }

        SimpleAclHandler::getInstance()->setValues(self::ACL_OBJECT_TYPE, $media->mediaID, $this->aclValues);
    }
}
