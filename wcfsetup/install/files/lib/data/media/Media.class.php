<?php

namespace wcf\data\media;

use wcf\data\CollectionDatabaseObject;
use wcf\data\ILinkableObject;
use wcf\data\IThumbnailFile;
use wcf\data\user\UserProfile;
use wcf\page\MediaPage;
use wcf\system\acl\simple\SimpleAclResolver;
use wcf\system\file\processor\IImageDataProvider;
use wcf\system\file\processor\ImageData;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Represents a media file.
 *
 * The localized title, caption and alternative text are stored in the
 * `wcf1_media_l10n` table.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $mediaID                unique id of the media file
 * @property-read   ?int    $categoryID             id of the category the media file belongs to or `null` if it belongs to no category
 * @property-read   string  $filename               name of the physical media file
 * @property-read   int     $filesize               size of the physical media file
 * @property-read   string  $fileType               type of the physical media file
 * @property-read   string  $fileHash               hash of the physical media file
 * @property-read   int     $uploadTime             timestamp at which the media file has been uploaded
 * @property-read   int     $fileUpdateTime         timestamp at which the media file was updated the last or `0` if it has not been updated
 * @property-read   ?int    $userID                 id of the user who uploaded the media file or null if the user does not exist anymore
 * @property-read   string  $username               name of the user who uploaded the media file
 * @property-read   ?int    $languageID             id of the language associated with the media file or null if the media file is multilingual or if the language has been deleted
 * @property-read   0|1     $isMultilingual         is `1` if the media file's title, description and altText is available in multiple languages, otherwise `0`
 * @property-read   0|1     $captionEnableHtml      is `1` if html code in caption is supported, otherwise `0`
 * @property-read   0|1     $isImage                is `1` if the media file is an image, otherwise `0`
 * @property-read   int     $width                  width of the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int     $height                 height of the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   string  $tinyThumbnailType      type of the tiny thumbnail file for the media file if `$isImage` is `1`, otherwise empty
 * @property-read   int     $tinyThumbnailSize      size of the tiny thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int     $tinyThumbnailWidth     width of the tiny thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int     $tinyThumbnailHeight    height of the tiny thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   string  $smallThumbnailType     type of the small thumbnail file for the media file if `$isImage` is `1`, otherwise empty
 * @property-read   int     $smallThumbnailSize     size of the small thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int     $smallThumbnailWidth    width of the small thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int     $smallThumbnailHeight   height of the small thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   string  $mediumThumbnailType    type of the medium thumbnail file for the media file if `$isImage` is `1`, otherwise empty
 * @property-read   int     $mediumThumbnailSize    size of the medium thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int     $mediumThumbnailWidth   width of the medium thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int     $mediumThumbnailHeight  height of the medium thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   string  $largeThumbnailType     type of the large thumbnail file for the media file if `$isImage` is `1`, otherwise empty
 * @property-read   int     $largeThumbnailSize     size of the large thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int     $largeThumbnailWidth    width of the large thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int     $largeThumbnailHeight   height of the large thumbnail file for the media file if `$isImage` is `1`, otherwise `0`
 * @property-read   int     $downloads              number of times the media file has been downloaded
 * @property-read   int     $lastDownloadTime       timestamp at which the media file has been downloaded the last time
 * @property-read   string  $title                  localized title of the media file
 * @property-read   string  $caption                localized caption of the media file
 * @property-read   string  $altText                localized alternative text of the media file
 *
 * @extends CollectionDatabaseObject<MediaCollection>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
class Media extends CollectionDatabaseObject implements
    ILinkableObject,
    IRouteController,
    IThumbnailFile,
    IImageDataProvider,
    \Stringable
{
    /**
     * names of the localized columns that are exposed as properties
     */
    private const L10N_COLUMNS = ['title', 'caption', 'altText'];

    /**
     * language id used to resolve the localized values instead of the active
     * user's language, see `getLocalizedVersion()`
     */
    private ?int $forcedLanguageID = null;

    /**
     * data of the different thumbnail sizes
     * @var array<string, array{
     *  height: int,
     *  retainDimensions: bool|int,
     *  width: int,
     * }>
     */
    protected static $thumbnailSizes = [
        'tiny' => [
            'height' => 144,
            'retainDimensions' => false,
            'width' => 144,
        ],
        'small' => [
            'height' => \MEDIA_SMALL_THUMBNAIL_HEIGHT,
            'retainDimensions' => \MEDIA_SMALL_THUMBNAIL_RETAIN_DIMENSIONS,
            'width' => \MEDIA_SMALL_THUMBNAIL_WIDTH,
        ],
        'medium' => [
            'height' => \MEDIA_MEDIUM_THUMBNAIL_HEIGHT,
            'retainDimensions' => \MEDIA_MEDIUM_THUMBNAIL_RETAIN_DIMENSIONS,
            'width' => \MEDIA_MEDIUM_THUMBNAIL_WIDTH,
        ],
        'large' => [
            'height' => \MEDIA_LARGE_THUMBNAIL_HEIGHT,
            'retainDimensions' => \MEDIA_LARGE_THUMBNAIL_RETAIN_DIMENSIONS,
            'width' => \MEDIA_LARGE_THUMBNAIL_WIDTH,
        ],
    ];

    #[\Override]
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getControllerLink(MediaPage::class, [
            'object' => $this,
        ]);
    }

    #[\Override]
    public function getLocation()
    {
        return self::getStorage() . \substr($this->fileHash, 0, 2) . '/' . $this->mediaID . '-' . $this->fileHash;
    }

    #[\Override]
    public function getThumbnailLink(string $size)
    {
        if (!isset(self::$thumbnailSizes[$size])) {
            throw new \InvalidArgumentException("Unknown thumbnail size '" . $size . "'");
        }

        if (!$this->{$size . 'ThumbnailType'}) {
            return $this->getLink();
        }

        return LinkHandler::getInstance()->getControllerLink(MediaPage::class, [
            'object' => $this,
            'thumbnail' => $size,
        ]);
    }

    /**
     * Returns the width of the thumbnail file with the given size.
     *
     * @return  int
     * @throws  \InvalidArgumentException
     */
    public function getThumbnailWidth(string $size)
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
     * @return  int
     * @throws  \InvalidArgumentException
     */
    public function getThumbnailHeight(string $size)
    {
        if (!isset(self::$thumbnailSizes[$size])) {
            throw new \InvalidArgumentException("Unknown thumbnail size '" . $size . "'");
        }

        if ($this->{$size . 'ThumbnailType'}) {
            return $this->{$size . 'ThumbnailHeight'};
        }

        return $this->height;
    }

    #[\Override]
    public function getThumbnailLocation(string $size)
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
     * Returns the localized title of the media file or its filename if it has
     * no title.
     */
    #[\Override]
    public function getTitle(): string
    {
        $title = $this->getL10nValue('title');
        if ($title !== '') {
            return $title;
        }

        return $this->filename;
    }

    /**
     * @since 6.3
     */
    public function getCaption(): string
    {
        return $this->getL10nValue('caption');
    }

    /**
     * @since 6.3
     */
    public function getAltText(): string
    {
        return $this->getL10nValue('altText');
    }

    /**
     * Returns the localized values of the given column as a
     * `languageID => value` map (see `L10nStorage`).
     *
     * @return L10nValue
     * @since 6.3
     */
    public function getL10nValues(string $columnName): array
    {
        if (!\in_array($columnName, self::L10N_COLUMNS, true)) {
            throw new \InvalidArgumentException("Invalid column name given.");
        }

        return $this->getCollection()->getL10nValues($this, $columnName);
    }

    /**
     * Returns a copy of this media file that resolves its localized values
     * for the given language instead of the active user's language.
     *
     * @since 6.3
     */
    public function getLocalizedVersion(int $languageID): static
    {
        $media = clone $this;
        $media->forcedLanguageID = $languageID;

        return $media;
    }

    private function getL10nValue(string $columnName): string
    {
        return L10nStorage::resolveValue($this->getL10nValues($columnName), $this->forcedLanguageID);
    }

    #[\Override]
    public function __get(string $name)
    {
        if (\in_array($name, self::L10N_COLUMNS, true)) {
            return $this->getL10nValue($name);
        }

        return parent::__get($name);
    }

    #[\Override]
    public function __isset(string $name)
    {
        if (\in_array($name, self::L10N_COLUMNS, true)) {
            return true;
        }

        return parent::__isset($name);
    }

    /**
     * Returns the icon name for this media file based on its filename.
     *
     * @since 6.3
     */
    public function getIconName(): string
    {
        if (($iconName = FileUtil::getIconNameByFilename($this->filename)) !== '') {
            return 'file-' . $iconName;
        }

        return 'file';
    }

    /**
     * Returns the i18n media data grouped by language id for all language.
     *
     * @return  string[][]
     * @deprecated 6.3 Use `getL10nValues()` instead.
     */
    public function getI18nData()
    {
        $i18nData = [];
        foreach (self::L10N_COLUMNS as $columnName) {
            $i18nData[$columnName] = $this->getL10nValues($columnName);
        }

        return $i18nData;
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
        if (WCF::getSession()->hasPermission('admin.content.cms.canManageMedia')) {
            if (WCF::getSession()->hasPermission('admin.content.cms.canOnlyAccessOwnMedia')) {
                return WCF::getUser()->userID === $this->userID;
            }

            return true;
        }

        return false;
    }

    /**
     * Returns true if a thumbnail version with the given size is available.
     *
     * @return  bool
     * @throws  \InvalidArgumentException
     */
    public function hasThumbnail(string $size)
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
     * Returns the user profile of the user who uploaded the media file.
     *
     * @since 6.3
     */
    public function getUserProfile(): UserProfile
    {
        return $this->getCollection()->getUserProfile($this);
    }

    /**
     * Returns a textual representation of the media file to be used in templates.
     *
     * @since 6.3
     */
    #[\Override]
    public function __toString(): string
    {
        if ($this->isImage !== 0) {
            return \sprintf(
                '<img src="%s" alt="%s" %s/>',
                StringUtil::encodeHTML($this->getLink()),
                StringUtil::encodeHTML($this->getAltText()),
                $this->getTitleAttribute(),
            );
        }

        return \sprintf(
            '<a href="%s">%s</a>',
            StringUtil::encodeHTML($this->getLink()),
            StringUtil::encodeHTML($this->getTitle()),
        );
    }

    /**
     * Returns a tag to display the media element in a square box of the given
     * size.
     *
     * @since 6.3
     */
    public function getElementTag(int $size): string
    {
        if ($this->isImage === 0) {
            return FontAwesomeIcon::fromValues($this->getIconName(), false)->toHtml($size);
        }

        $width = $size;
        $height = $size;
        $link = null;
        $marginTop = 0;

        if ($this->tinyThumbnailType !== '') {
            $link = $this->getThumbnailLink('tiny');

            if ($size <= $this->tinyThumbnailWidth && $size <= $this->tinyThumbnailHeight) {
                if ($this->tinyThumbnailHeight < $this->tinyThumbnailWidth) {
                    $height = \round($this->tinyThumbnailHeight / $this->tinyThumbnailWidth * $size);
                } else {
                    $width = \round($this->tinyThumbnailWidth / $this->tinyThumbnailHeight * $size);
                }
            } elseif ($size <= $this->tinyThumbnailWidth) {
                $height = \round($this->tinyThumbnailHeight / $this->tinyThumbnailWidth * $size);
            } elseif ($size <= $this->tinyThumbnailHeight) {
                $width = \round($this->tinyThumbnailWidth / $this->tinyThumbnailHeight * $size);
            } else {
                $link = null;
            }

            $marginTop = \floor(($size - $height) / 2);
        }

        if ($link === null) {
            $link = $this->getLink();

            if ($size <= $this->width && $size <= $this->height) {
                if ($this->height < $this->width) {
                    $height = \round($this->height / $this->width * $size);
                } else {
                    $width = \round($this->width / $this->height * $size);
                }
            } elseif ($size <= $this->width) {
                $height = \round($this->height / $this->width * $size);
            } elseif ($size <= $this->height) {
                $width = \round($this->width / $this->height * $size);
            } else {
                $width = $this->width;
                $height = $this->height;
            }

            $marginTop = \floor(($size - $height) / 2);
        }

        return \sprintf(
            '<span style="display: inline-block; text-align: center; width: %1$dpx; height: %1$dpx;">'
                . '<img src="%2$s" alt="%3$s" %4$sstyle="margin-top: %5$dpx;" height="%6$d" width="%7$d" loading="lazy">'
                . '</span>',
            $size,
            StringUtil::encodeHTML($link),
            StringUtil::encodeHTML($this->getAltText()),
            $this->getTitleAttribute(),
            $marginTop,
            $height,
            $width,
        );
    }

    /**
     * Returns a tag to display the thumbnail with the given size.
     *
     * @since 6.3
     */
    public function getThumbnailTag(string $size = 'tiny'): string
    {
        if (!isset(self::$thumbnailSizes[$size])) {
            throw new \InvalidArgumentException("Unknown thumbnail size '" . $size . "'");
        }

        return \sprintf(
            '<img src="%s" alt="%s" width="%d" height="%d" loading="lazy" %s>',
            StringUtil::encodeHTML($this->getThumbnailLink($size)),
            StringUtil::encodeHTML($this->getAltText()),
            $this->getThumbnailWidth($size),
            $this->getThumbnailHeight($size),
            $this->getTitleAttribute()
        );
    }

    /**
     * Returns the `title` attribute of the media file or an empty string if it has no title.
     */
    private function getTitleAttribute(): string
    {
        $title = $this->getL10nValue('title');
        if ($title === '') {
            return '';
        }

        return 'title="' . StringUtil::encodeHTML($title) . '" ';
    }

    /**
     * Returns the storage path of the media files.
     *
     * @return  string
     */
    public static function getStorage()
    {
        return \WCF_DIR . 'media_files/';
    }

    #[\Override]
    public static function getThumbnailSizes()
    {
        return static::$thumbnailSizes;
    }

    #[\Override]
    public function getImageData(?int $minWidth = null, ?int $minHeight = null): ?ImageData
    {
        if ($this->isImage === 0) {
            return null;
        }

        if (!$this->isAccessible()) {
            return null;
        }

        if ($minWidth !== null || $minHeight !== null) {
            foreach (\array_keys(self::$thumbnailSizes) as $size) {
                if ($minWidth !== null && $minWidth > $this->getThumbnailWidth($size)) {
                    continue;
                }
                if ($minHeight !== null && $minHeight > $this->getThumbnailHeight($size)) {
                    continue;
                }

                return new ImageData($this->getThumbnailLink($size), $this->getThumbnailWidth($size), $this->getThumbnailHeight($size));
            }

            if ($minWidth !== null && $minWidth > $this->width) {
                return null;
            }
            if ($minHeight !== null && $minHeight > $this->height) {
                return null;
            }
        }

        return new ImageData($this->getLink(), $this->width, $this->height);
    }

    /**
     * @since 6.3
     */
    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_media',
            'wcf1_media_l10n',
            'mediaID',
            ['title', 'caption', 'altText'],
            maximumLengths: ['title' => 255, 'altText' => 255],
        );
    }
}
