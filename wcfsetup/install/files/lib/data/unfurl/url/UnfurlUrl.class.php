<?php

namespace wcf\data\unfurl\url;

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
 * @package     WoltLabSuite\Core\Data\Unfurl\Url
 * @since       5.4
 *
 * @property-read string $url
 * @property-read string $urlHash
 * @property-read string $title
 * @property-read string $description
 * @property-read string $imageHash
 * @property-read string $imageUrl
 * @property-read string $imageType
 */
class UnfurlUrl extends DatabaseObject
{
    public const IMAGE_SQUARED = "SQUARED";

    public const IMAGE_COVER = "COVER";

    public const IMAGE_NO_IMAGE = "NOIMAGE";

    public const STATUS_PENDING = "PENDING";

    public const STATUS_SUCCESSFUL = "SUCCESSFUL";

    public const STATUS_REJECTED = "REJECTED";

    /**
     * Renders the unfurl url card and returns the template.
     *
     * @return string
     */
    public function render(): string
    {
        return WCF::getTPL()->fetch('unfurlUrl', 'wcf', [
            'object' => $this,
        ]);
    }

    /**
     * Returns the hostname of the url.
     *
     * @return string
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
        if (!empty($this->imageHash)) {
            return WCF::getPath() . 'images/unfurlUrl/' . \substr($this->imageHash, 0, 2) . '/' . $this->imageHash;
        } elseif (!empty($this->imageUrl)) {
            if (MODULE_IMAGE_PROXY) {
                $key = CryptoUtil::createSignedString($this->imageUrl);

                return LinkHandler::getInstance()->getLink('ImageProxy', [
                    'key' => $key,
                ]);
            } elseif (IMAGE_ALLOW_EXTERNAL_SOURCE) {
                return $this->imageUrl;
            }
        }

        return null;
    }

    /**
     * Returns the unfurl url object for a given url.
     *
     * @throws \InvalidArgumentException If the given URL is invalid.
     */
    public static function getByUrl(string $url): self
    {
        if (!Url::is($url)) {
            throw new \InvalidArgumentException("Given URL is not a valid URL.");
        }

        $sql = "SELECT		unfurl_url.*
				FROM		wcf" . WCF_N . "_unfurl_url unfurl_url
				WHERE		unfurl_url.urlHash = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([\sha1($url)]);
        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }
}
