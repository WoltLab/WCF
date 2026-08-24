<?php

namespace wcf\data\unfurl\url\image;

use wcf\action\ImageProxyAction;
use wcf\data\CollectionDatabaseObject;
use wcf\data\file\File;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\CryptoUtil;

/**
 * Represents the image of an unfurled url.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @property-read   int     $imageID
 * @property-read   string  $imageUrl
 * @property-read   ?string $imageUrlHash
 * @property-read   int     $width
 * @property-read   int     $height
 * @property-read   ?string $imageExtension deprecated, only in use to migrate old files
 * @property-read   int     $isStored
 * @property-read   ?int    $fileID
 *
 * @extends CollectionDatabaseObject<UnfurlUrlImageCollection>
 */
class UnfurlUrlImage extends CollectionDatabaseObject
{
    /**
     * Returns the stored file of this image or `null` if the image has not
     * been stored locally.
     */
    public function getFile(): ?File
    {
        if ($this->isStored === 0 || $this->fileID === null) {
            return null;
        }

        return $this->getCollection()->getFile($this);
    }

    /**
     * Returns the image for the given image url or `null` if there is no such image.
     */
    public static function getByImageUrl(string $imageUrl): ?self
    {
        $sql = "SELECT  *
                FROM    wcf1_unfurl_url_image
                WHERE   imageUrlHash = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([\sha1($imageUrl)]);

        return $statement->fetchSingleObject(self::class);
    }

    public function getImageUrl(): ?string
    {
        if (\URL_UNFURLING_SAVE_IMAGES !== 0 && $this->isStored !== 0 && $this->fileID !== null) {
            $file = $this->getCollection()->getFile($this);

            return 'data:image/webp;base64, ' . \file_get_contents($file->getPathname());
        } elseif ($this->imageUrl !== '') {
            if (\MODULE_IMAGE_PROXY !== 0) {
                $key = CryptoUtil::createSignedString($this->imageUrl);

                return LinkHandler::getInstance()->getControllerLink(ImageProxyAction::class, [
                    'key' => $key,
                ]);
            } elseif (\IMAGE_ALLOW_EXTERNAL_SOURCE !== 0) {
                return $this->imageUrl;
            }
        }

        return null;
    }

    public function hasImageUrl(): bool
    {
        if (\URL_UNFURLING_SAVE_IMAGES !== 0 && $this->isStored !== 0 && $this->fileID !== null) {
            return true;
        } elseif ($this->imageUrl !== '' && (\MODULE_IMAGE_PROXY !== 0 || \IMAGE_ALLOW_EXTERNAL_SOURCE !== 0)) {
            return true;
        }

        return false;
    }
}
