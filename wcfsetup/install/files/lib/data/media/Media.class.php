<?php

namespace wcf\data\media;

use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\data\IThumbnailFile;
use wcf\system\acl\simple\SimpleAclResolver;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a media file.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @property-read   int $mediaID        unique id of the media file
 * @property-read   int $categoryID     id of the category the media file belongs to or `null` if it belongs to no category
 * @property-read   string $filename       name of the physical media file
 * @property-read   int $filesize       size of the physical media file
 * @property-read   string $fileType       type of the physical media file
 * @property-read   string $fileHash       hash of the physical media file
 * @property-read   int $uploadTime     timestamp at which the media file has been uploaded
 * @property-read       int $fileUpdateTime         timestamp at which the media file was updated the last or `0` if it has not been updated
 * @property-read   int|null $userID         id of the user who uploaded the media file or null if the user does not exist anymore
 * @property-read   string $username       name of the user who uploaded the media file
 * @property-read   int|null $languageID     id of the language associated with the media file or null if the media file is multilingual or if the language has been deleted
 * @property-read   int $isMultilingual     is `1` if the media file's title, description and altText is available in multiple languages, otherwise `0`
 * @property-read   int $captionEnableHtml  is `1` if html code in caption is supported, otherwise `0`
 * @property-read   int $isImage        is `1` if the media file is an image, otherwise `0`
 * @property-read   int $width          width of the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int $height         height of the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   string $tinyThumbnailType  type of the tiny thumbnail file for the media file if `$isImage` is `1`, otherwise empty
 * @property-read   int $tinyThumbnailSize  size of the tiny thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int $tinyThumbnailWidth width of the tiny thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int $tinyThumbnailHeight    height of the tiny thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   string $smallThumbnailType type of the small thumbnail file for the media file if `$isImage` is `1`, otherwise empty
 * @property-read   int $smallThumbnailSize size of the small thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int $smallThumbnailWidth    width of the small thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int $smallThumbnailHeight   height of the small thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   string $mediumThumbnailType    type of the medium thumbnail file for the media file if `$isImage` is `1`, otherwise empty
 * @property-read   int $mediumThumbnailSize    size of the medium thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int $mediumThumbnailWidth   width of the medium thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int $mediumThumbnailHeight  height of the medium thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   string $largeThumbnailType type of the large thumbnail file for the media file if `$isImage` is `1`, otherwise empty
 * @property-read   int $largeThumbnailSize size of the large thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int $largeThumbnailWidth    width of the large thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int $largeThumbnailHeight   height of the large thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int $downloads      number of times the media file has been downloaded
 * @property-read   int $lastDownloadTime   timestamp at which the media file has been downloaded the last time
 */
class Media extends DatabaseObject implements ILinkableObject, IRouteController, IThumbnailFile
{
    /**
     * i18n media data grouped by language id for all language
     * @var string[][]
     */
    protected $i18nData;

    /**
     * data of the different thumbnail sizes
     * @var array
     */
    protected static $thumbnailSizes = [
        'tiny' => [
            'height' => 144,
            'retainDimensions' => false,
            'width' => 144,
        ],
        'small' => [
            'height' => MEDIA_SMALL_THUMBNAIL_HEIGHT,
            'retainDimensions' => MEDIA_SMALL_THUMBNAIL_RETAIN_DIMENSIONS,
            'width' => MEDIA_SMALL_THUMBNAIL_WIDTH,
        ],
        'medium' => [
            'height' => MEDIA_MEDIUM_THUMBNAIL_HEIGHT,
            'retainDimensions' => MEDIA_MEDIUM_THUMBNAIL_RETAIN_DIMENSIONS,
            'width' => MEDIA_MEDIUM_THUMBNAIL_WIDTH,
        ],
        'large' => [
            'height' => MEDIA_LARGE_THUMBNAIL_HEIGHT,
            'retainDimensions' => MEDIA_LARGE_THUMBNAIL_RETAIN_DIMENSIONS,
            'width' => MEDIA_LARGE_THUMBNAIL_WIDTH,
        ],
    ];

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getLink('Media', [
            'object' => $this,
            'forceFrontend' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getLocation()
    {
        return self::getStorage() . \substr($this->fileHash, 0, 2) . '/' . $this->mediaID . '-' . $this->fileHash;
    }

    /**
     * @inheritDoc
     */
    public function getThumbnailLink($size)
    {
        if (!isset(self::$thumbnailSizes[$size])) {
            throw new \InvalidArgumentException("Unknown thumbnail size '" . $size . "'");
        }

        if (!$this->{$size . 'ThumbnailType'}) {
            return $this->getLink();
        }

        return LinkHandler::getInstance()->getLink('Media', [
            'object' => $this,
            'forceFrontend' => true,
            'thumbnail' => $size,
        ]);
    }

    /**
     * Returns the width of the thumbnail file with the given size.
     *
     * @param string $size
     * @return  int
     * @throws  \InvalidArgumentException
     */
    public function getThumbnailWidth($size)
    {
        if (!isset(self::$thumbnailSizes[$size])) {
            throw new \InvalidArgumentException("Unknown thumbnail size '" . $size . "'");
        }

        if ($this->{$size . 'ThumbnailType'}) {
            return $this->{$size . 'ThumbnailWidth'};
        }

        return $this->width;
    }

    /**
     * Returns the height of the thumbnail file with the given size.
     *
     * @param string $size
     * @return  int
     * @throws  \InvalidArgumentException
     */
    public function getThumbnailHeight($size)
    {
        if (!isset(self::$thumbnailSizes[$size])) {
            throw new \InvalidArgumentException("Unknown thumbnail size '" . $size . "'");
        }

        if ($this->{$size . 'ThumbnailType'}) {
            return $this->{$size . 'ThumbnailHeight'};
        }

        return $this->height;
    }

    /**
     * @inheritDoc
     */
    public function getThumbnailLocation($size)
    {
        if (!isset(self::$thumbnailSizes[$size])) {
            throw new \InvalidArgumentException("Unknown thumbnail size '" . $size . "'");
        }

        return self::getStorage() . \substr(
            $this->fileHash,
            0,
            2
        ) . '/' . $this->mediaID . '-' . $size . '-' . $this->fileHash;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->filename;
    }

    /**
     * Returns the i18n media data grouped by language id for all language.
     *
     * @return  string[][]
     */
    public function getI18nData()
    {
        if ($this->i18nData === null) {
            $this->i18nData = [
                'altText' => [],
                'caption' => [],
                'title' => [],
            ];

            $sql = "SELECT  *
                    FROM    wcf" . WCF_N . "_media_content
                    WHERE   mediaID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$this->mediaID]);

            while ($row = $statement->fetchArray()) {
                $this->i18nData['altText'][$row['languageID']] = $row['altText'];
                $this->i18nData['caption'][$row['languageID']] = $row['caption'];
                $this->i18nData['title'][$row['languageID']] = $row['title'];
            }
        }

        return $this->i18nData;
    }

    /**
     * Returns true if the media file can be accessed by the active user.
     *
     * @return  bool
     */
    public function isAccessible()
    {
        if ($this->canManage()) {
            return true;
        }

        return SimpleAclResolver::getInstance()->canAccess('com.woltlab.wcf.media', $this->mediaID);
    }

    /**
     * Returns `true` if the active user can manage this media file.
     *
     * @return  bool
     */
    public function canManage()
    {
        if (WCF::getSession()->getPermission('admin.content.cms.canManageMedia')) {
            if (WCF::getSession()->getPermission('admin.content.cms.canOnlyAccessOwnMedia')) {
                return WCF::getUser()->userID == $this->userID;
            }

            return true;
        }

        return false;
    }

    /**
     * Returns true if a thumbnail version with the given size is available.
     *
     * @param string $size
     * @return  bool
     * @throws  \InvalidArgumentException
     */
    public function hasThumbnail($size)
    {
        if (!isset(self::$thumbnailSizes[$size])) {
            throw new \InvalidArgumentException("Unknown thumbnail size '" . $size . "'");
        }

        if ($this->{$size . 'ThumbnailType'}) {
            return true;
        }

        if ($this->width <= self::$thumbnailSizes[$size]['width'] && $this->height <= self::$thumbnailSizes[$size]['height']) {
            return true;
        }

        return false;
    }

    /**
     * @since 6.0
     */
    public function isVideo(): bool
    {
        return \str_starts_with($this->fileType, 'video/');
    }

    /**
     * @since 6.0
     */
    public function isAudio(): bool
    {
        return \str_starts_with($this->fileType, 'audio/');
    }

    /**
     * Returns the storage path of the media files.
     *
     * @return  string
     */
    public static function getStorage()
    {
        return WCF_DIR . 'media_files/';
    }

    /**
     * @inheritDoc
     */
    public static function getThumbnailSizes()
    {
        return static::$thumbnailSizes;
    }
}
