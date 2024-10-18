<?php

namespace wcf\data\unfurl\url;

use wcf\action\ImageProxyAction;
use wcf\data\DatabaseObject;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\CryptoUtil;
use wcf\util\Url;

/**
 * Represents an unfurl url object in the database.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 *
 * @property-read int $urlID
 * @property-read string $url
 * @property-read string $urlHash
 * @property-read string $title
 * @property-read string $description
 * @property-read string $imageHash
 * @property-read string $imageUrl
 * @property-read string $imageUrlHash
 * @property-read string $imageExtension
 * @property-read int $width
 * @property-read int $height
 * @property-read int $lastFetch
 * @property-read int $imageID
 * @property-read int $isStored
 * @property-read string $status
 */
class UnfurlUrl extends DatabaseObject
{
    private const IMAGE_SQUARED = "SQUARED";

    private const IMAGE_COVER = "COVER";

    private const IMAGE_NO_IMAGE = "NOIMAGE";

    public const STATUS_PENDING = "PENDING";

    public const STATUS_SUCCESSFUL = "SUCCESSFUL";

    public const STATUS_REJECTED = "REJECTED";

    public const IMAGE_DIR = "images/unfurlUrl/";

    /**
     * @inheritDoc
     */
    public function __construct($id, $row = null, ?DatabaseObject $object = null)
    {
        if ($id !== null) {
            $sql = "SELECT      unfurl_url.*, unfurl_url_image.*
                    FROM        wcf1_unfurl_url unfurl_url
                    LEFT JOIN   wcf1_unfurl_url_image unfurl_url_image
                    ON          unfurl_url_image.imageID = unfurl_url.imageID
                    WHERE       unfurl_url.urlID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$id]);
            $row = $statement->fetchArray();

            // enforce data type 'array'
            if ($row === false) {
                $row = [];
            }
        } elseif ($object !== null) {
            $row = $object->data;
        }

        $this->handleData($row);
    }

    /**
     * Renders the unfurl url card and returns the template.
     */
    public function render(bool $enableUgc = true): string
    {
        return WCF::getTPL()->fetch('shared_unfurlUrl', 'wcf', [
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

    /**
     * Returns the image url for the url.
     *
     * @throws \wcf\system\exception\SystemException
     */
    public function getImageUrl(): ?string
    {
        if ($this->isStored) {
            $imageFolder = self::IMAGE_DIR . \substr($this->imageUrlHash, 0, 2) . "/";
            $imageName = $this->imageUrlHash . '.' . $this->imageExtension;

            return WCF::getPath() . $imageFolder . $imageName;
        } elseif (!empty($this->imageUrl)) {
            if (MODULE_IMAGE_PROXY) {
                $key = CryptoUtil::createSignedString($this->imageUrl);

                return LinkHandler::getInstance()->getControllerLink(ImageProxyAction::class, [
                    'key' => $key,
                ]);
            } elseif (IMAGE_ALLOW_EXTERNAL_SOURCE) {
                return $this->imageUrl;
            }
        }

        return null;
    }

    public function hasCoverImage(): bool
    {
        return $this->getImageType() === self::IMAGE_COVER && !empty($this->getImageUrl());
    }

    public function hasSquaredImage(): bool
    {
        return $this->getImageType() === self::IMAGE_SQUARED && !empty($this->getImageUrl());
    }

    public function isPlainUrl(): bool
    {
        return empty($this->description) && empty($this->imageID);
    }

    private function getImageType(): string
    {
        if (!$this->imageID) {
            return self::IMAGE_NO_IMAGE;
        }

        if ($this->width === $this->height) {
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
     * Returns the unfurl url object for a given url.
     *
     * @throws \InvalidArgumentException If the given URL is invalid.
     */
    public static function getByUrl(string $url): ?self
    {
        if (!Url::is($url)) {
            throw new \InvalidArgumentException("Given URL is not a valid URL.");
        }

        $sql = "SELECT      unfurl_url.*, unfurl_url_image.*
                FROM        wcf1_unfurl_url unfurl_url
                LEFT JOIN   wcf1_unfurl_url_image unfurl_url_image
                ON          unfurl_url_image.imageID = unfurl_url.imageID
                WHERE       unfurl_url.urlHash = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([\sha1($url)]);
        $row = $statement->fetchArray();
        if (!$row) {
            return null;
        }

        return new self(null, $row);
    }
}
