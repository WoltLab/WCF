<?php

namespace wcf\data\attachment;

use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\data\IThumbnailFile;
use wcf\data\file\File;
use wcf\data\file\thumbnail\FileThumbnailList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Represents an attachment.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $attachmentID       unique id of the attachment
 * @property-read   int $objectTypeID       id of the `com.woltlab.wcf.attachment.objectType` object type
 * @property-read   int|null $objectID       id of the attachment container object the attachment belongs to
 * @property-read   int|null $userID         id of the user who uploaded the attachment or `null` if the user does not exist anymore or if the attachment has been uploaded by a guest
 * @property-read   string $tmpHash        temporary hash used to identify uploaded attachments but not associated with an object yet or empty if the attachment has been associated with an object
 * @property-read   string $filename       name of the physical attachment file
 * @property-read   int $filesize       size of the physical attachment file
 * @property-read   string $fileType       type of the physical attachment file
 * @property-read   string $fileHash       hash of the physical attachment file
 * @property-read   int $isImage        is `1` if the attachment is an image, otherwise `0`
 * @property-read   int $width          width of the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read   int $height         height of the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read   string $tinyThumbnailType  type of the tiny thumbnail file for the attachment if `$isImage` is `1`, otherwise empty
 * @property-read   int $tinyThumbnailSize  size of the tiny thumbnail file for the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read   int $tinyThumbnailWidth width of the tiny thumbnail file for the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read   int $tinyThumbnailHeight    height of the tiny thumbnail file for the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read   string $thumbnailType  type of the thumbnail file for the attachment if `$isImage` is `1`, otherwise empty
 * @property-read   int $thumbnailSize  size of the thumbnail file for the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read   int $thumbnailWidth width of the thumbnail file for the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read   int $thumbnailHeight    height of the thumbnail file for the attachment if `$isImage` is `1`, otherwise `0`
 * @property-read   int $uploadTime     timestamp at which the attachment has been uploaded
 * @property-read   int $showOrder      position of the attachment in relation to the other attachment to the same message
 * @property-read int|null $fileID
 * @property-read int|null $thumbnailID
 * @property-read int|null $tinyThumbnailID
 */
class Attachment extends DatabaseObject implements ILinkableObject, IRouteController, IThumbnailFile
{
    /**
     * indicates if the attachment is embedded
     * @var bool
     */
    protected $embedded = false;

    /**
     * user permissions for attachment access
     * @var bool[]
     */
    protected $permissions = [];

    protected File $file;

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        $file = $this->getFile();
        if ($file !== null) {
            return $file->getLink();
        }

        // Do not use `LinkHandler::getControllerLink()` or `forceFrontend` as attachment
        // links can be opened in the frontend and in the ACP.
        return LinkHandler::getInstance()->getLink('Attachment', [
            'object' => $this,
        ]);
    }

    /**
     * Returns true if a user has the permission to download this attachment.
     *
     * @return  bool
     */
    public function canDownload()
    {
        return $this->getPermission('canDownload');
    }

    /**
     * Returns true if a user has the permission to view the preview of this
     * attachment.
     *
     * @return  bool
     */
    public function canViewPreview()
    {
        return $this->getPermission('canViewPreview');
    }

    /**
     * Returns true if a user has the permission to delete the preview of this
     * attachment.
     *
     * @return  bool
     */
    public function canDelete()
    {
        return $this->getPermission('canDelete');
    }

    /**
     * Checks permissions.
     *
     * @param string $permission
     * @return  bool
     */
    protected function getPermission($permission)
    {
        if (!isset($this->permissions[$permission])) {
            $this->permissions[$permission] = true;

            if ($this->tmpHash) {
                if ($this->userID && $this->userID != WCF::getUser()->userID) {
                    $this->permissions[$permission] = false;
                }
            } else {
                $objectType = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
                $processor = $objectType->getProcessor();
                if ($processor !== null) {
                    $this->permissions[$permission] = \call_user_func([$processor, $permission], $this->objectID);
                }
            }
        }

        return $this->permissions[$permission];
    }

    /**
     * Sets the permissions for attachment access.
     *
     * @param bool[] $permissions
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @inheritDoc
     */
    public function getLocation()
    {
        return $this->getLocationHelper(self::getStorage() . \substr(
            $this->fileHash,
            0,
            2
        ) . '/' . $this->attachmentID . '-' . $this->fileHash);
    }

    /**
     * Returns the physical location of the tiny thumbnail.
     *
     * @return  string
     */
    public function getTinyThumbnailLocation()
    {
        return $this->getThumbnailLocation('tiny');
    }

    /**
     * @inheritDoc
     */
    public function getThumbnailLocation($size = '')
    {
        if ($size == 'tiny') {
            $location = self::getStorage() . \substr(
                $this->fileHash,
                0,
                2
            ) . '/' . $this->attachmentID . '-tiny-' . $this->fileHash;
        } else {
            $location = self::getStorage() . \substr(
                $this->fileHash,
                0,
                2
            ) . '/' . $this->attachmentID . '-thumbnail-' . $this->fileHash;
        }

        return $this->getLocationHelper($location);
    }

    /**
     * Migrates the storage location of this attachment to
     * include the `.bin` suffix.
     *
     * @since   5.2
     */
    public function migrateStorage()
    {
        foreach ([$this->getLocation(), $this->getThumbnailLocation(), $this->getThumbnailLocation('tiny'),] as $location) {
            if (!\str_ends_with($location, '.bin')) {
                \rename($location, $location . '.bin');
            }
        }
    }

    /**
     * Returns the appropriate location with or without extension.
     *
     * Files are suffixed with `.bin` since 5.2, but they are recognized
     * without the extension for backward compatibility.
     *
     * @param string $location
     * @return  string
     * @since   5.2
     */
    final protected function getLocationHelper($location)
    {
        if (\is_readable($location . '.bin')) {
            return $location . '.bin';
        } elseif (\is_readable($location)) {
            return $location;
        }

        // Assume that the attachment has not been uploaded yet.
        return $location . '.bin';
    }

    /**
     * @inheritDoc
     */
    public function getThumbnailLink($size = '')
    {
        $file = $this->getFile();
        if ($file === null) {
            return '';
        }

        if ($size === '') {
            return $file->getLink();
        }

        $thumbnail = $file->getThumbnail($size !== 'tiny' ? '' : $size);
        if ($this === null) {
            return '';
        }

        return $thumbnail->getLink();
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->filename;
    }

    /**
     * Marks this attachment as embedded.
     */
    public function markAsEmbedded()
    {
        $this->embedded = true;
    }

    /**
     * Returns true if this attachment is embedded.
     *
     * @return  bool
     */
    public function isEmbedded()
    {
        return $this->embedded;
    }

    /**
     * Returns true if this attachment should be shown as an image.
     *
     * @return  bool
     */
    public function showAsImage()
    {
        if ($this->isImage) {
            if (!$this->hasThumbnail() && ($this->width > \ATTACHMENT_THUMBNAIL_WIDTH || $this->height > \ATTACHMENT_THUMBNAIL_HEIGHT)) {
                // Some images have no thumbnail because this is not really an
                // image or it is unrecognized. However, there are images that
                // do not have a thumbnail because one of its dimensions is
                // less then the thumbnails minimum of that side.
                $shouldHaveThumbnail = true;
                if ($this->width > \ATTACHMENT_THUMBNAIL_WIDTH && $this->height < \ATTACHMENT_THUMBNAIL_HEIGHT) {
                    $shouldHaveThumbnail = false;
                } else if ($this->height > ATTACHMENT_THUMBNAIL_HEIGHT && $this->width < \ATTACHMENT_THUMBNAIL_WIDTH) {
                    $shouldHaveThumbnail = false;
                }

                if ($shouldHaveThumbnail) {
                    return false;
                }
            }

            if ($this->canDownload()) {
                return true;
            }

            if ($this->canViewPreview() && $this->hasThumbnail()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if this attachment has a thumbnail.
     *
     * @return  bool
     */
    public function hasThumbnail()
    {
        return $this->thumbnailType ? true : false;
    }

    /**
     * Returns true if this attachment should be shown as a file.
     *
     * @return  bool
     */
    public function showAsFile()
    {
        return !$this->showAsImage();
    }

    /**
     * Returns icon name for this attachment.
     *
     * @return      string
     */
    public function getIconName()
    {
        if ($iconName = FileUtil::getIconNameByFilename($this->filename)) {
            return 'file-' . $iconName;
        }

        return 'paperclip';
    }

    public function getFile(): ?File
    {
        // This method is called within `__get()`, therefore we must dereference
        // the data array directly to avoid recursion.
        $fileID = $this->data['fileID'] ?? null;

        if (!$fileID) {
            return null;
        }

        if (!isset($this->file)) {
            $this->file = new File($fileID);

            $thumbnailList = new FileThumbnailList();
            $thumbnailList->getConditionBuilder()->add("fileID = ?", [$this->file->fileID]);
            $thumbnailList->readObjects();
            foreach ($thumbnailList as $thumbnail) {
                $this->file->addThumbnail($thumbnail);
            }
        }

        return $this->file;
    }

    public function setFile(File $file): void
    {
        if ($this->file->fileID === $file->fileID) {
            $this->file = $file;
        }
    }

    #[\Override]
    public function __get($name)
    {
        // Deprecated attributes that are no longer supported.
        $value = match ($name) {
            'downloads' => 0,
            'lastDownloadTime' => 0,
            default => null,
        };
        if ($value !== null) {
            return $value;
        }

        $file = $this->getFile();
        if ($file === null) {
            return parent::__get($name);
        }

        return match ($name) {
            'filename' => $file->filename,
            'filesize' => $file->fileSize,
            'fileType' => $file->mimeType,
            'isImage' => $file->isImage(),
            'height' => $file->height,
            'width' => $file->width,
            'thumbnailType' => $file->getThumbnail('')?->getMimeType() ?: '',
            'thumbnailWidth' => $file->getThumbnail('')?->width ?: 0,
            'thumbnailHeight' => $file->getThumbnail('')?->height ?: 0,
            'tinyThumbnailType' => $file->getThumbnail('tiny')?->getMimeType() ?: '',
            'tinyThumbnailWidth' => $file->getThumbnail('tiny')?->width ?: 0,
            'tinyThumbnailHeight' => $file->getThumbnail('tiny')?->height ?: 0,
            default => parent::__get($name),
        };
    }

    public function toHtmlElement(): ?string
    {
        return $this->getFile()?->toHtmlElement([
            'attachmentID' => $this->attachmentID,
        ]);
    }

    public static function findByFileID(int $fileID): ?Attachment
    {
        $sql = "SELECT  *
                FROM    wcf1_attachment
                WHERE   fileID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$fileID]);

        return $statement->fetchObject(Attachment::class);
    }

    /**
     * Returns the storage path.
     */
    public static function getStorage(): string
    {
        return WCF_DIR . 'attachments/';
    }

    /**
     * @inheritDoc
     */
    public static function getThumbnailSizes()
    {
        return [
            'tiny' => [
                'height' => 144,
                'retainDimensions' => false,
                'width' => 144,
            ],
            // standard thumbnail size
            '' => [
                'height' => ATTACHMENT_THUMBNAIL_HEIGHT,
                'retainDimensions' => ATTACHMENT_RETAIN_DIMENSIONS,
                'width' => ATTACHMENT_THUMBNAIL_WIDTH,
            ],
        ];
    }
}
