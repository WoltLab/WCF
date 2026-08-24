<?php

namespace wcf\data\unfurl\url;

use wcf\data\CollectionDatabaseObject;
use wcf\data\unfurl\url\image\UnfurlUrlImage;
use wcf\system\WCF;
use wcf\util\Url;

/**
 * Represents an unfurl url object in the database.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 *
 * @property-read   int     $urlID
 * @property-read   string  $url
 * @property-read   string  $urlHash
 * @property-read   string  $title
 * @property-read   ?string $description
 * @property-read   ?int    $imageID
 * @property-read   string  $status
 * @property-read   int     $lastFetch
 *
 * @extends CollectionDatabaseObject<UnfurlUrlCollection>
 */
class UnfurlUrl extends CollectionDatabaseObject
{
    private const IMAGE_SQUARED = "SQUARED";

    private const IMAGE_COVER = "COVER";

    private const IMAGE_NO_IMAGE = "NOIMAGE";

    public const STATUS_PENDING = "PENDING";

    public const STATUS_SUCCESSFUL = "SUCCESSFUL";

    public const STATUS_REJECTED = "REJECTED";

    /**
     * @deprecated 6.2
     */
    public const IMAGE_DIR = "images/unfurlUrl/";
    public const THUMBNAIL_WIDTH = 800;
    public const THUMBNAIL_HEIGHT = 400;

    /**
     * Renders the unfurl url card and returns the template.
     */
    public function render(bool $enableUgc = true): string
    {
        return WCF::getTPL()->render('wcf', 'shared_unfurlUrl', [
            'object' => $this,
            'enableUgc' => $enableUgc,
        ]);
    }

    /**
     * Returns the hostname of the url.
     */
    public function getHost(): string
    {
        $url = Url::parse($this->url);

        return $url['host'];
    }

    public function getImageUrl(): ?string
    {
        if ($this->imageID === null) {
            return null;
        }

        return $this->getImage()->getImageUrl();
    }

    public function hasImageUrl(): bool
    {
        if ($this->imageID === null) {
            return false;
        }

        return $this->getImage()->hasImageUrl();
    }

    public function hasCoverImage(): bool
    {
        return $this->getImageType() === self::IMAGE_COVER && $this->hasImageUrl();
    }

    public function hasSquaredImage(): bool
    {
        return $this->getImageType() === self::IMAGE_SQUARED && $this->hasImageUrl();
    }

    public function isPlainUrl(): bool
    {
        return ($this->description ?? '') === '' && $this->imageID === null;
    }

    private function getImageType(): string
    {
        if ($this->imageID === null) {
            return self::IMAGE_NO_IMAGE;
        }

        if ($this->getImage()->width === $this->getImage()->height) {
            return self::IMAGE_SQUARED;
        }

        return self::IMAGE_COVER;
    }

    /**
     * @since 6.0
     */
    public function hasFetchedContent(): bool
    {
        return $this->status === self::STATUS_SUCCESSFUL;
    }

    /**
     * @since 6.3
     */
    public function getImage(): ?UnfurlUrlImage
    {
        return $this->getCollection()->getImage($this);
    }

    /**
     * Returns the unfurl url object for a given url.
     *
     * @throws \InvalidArgumentException If the given URL is invalid.
     */
    public static function getByUrl(string $url): ?self
    {
        if (!Url::is($url)) {
            throw new \InvalidArgumentException("Given URL is not a valid URL.");
        }

        $sql = "SELECT  *
                FROM    wcf1_unfurl_url
                WHERE   urlHash = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([\sha1($url)]);

        return $statement->fetchSingleObject(self::class);
    }
}
