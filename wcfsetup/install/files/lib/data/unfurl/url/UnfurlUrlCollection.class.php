<?php

namespace wcf\data\unfurl\url;

use wcf\data\DatabaseObjectCollection;
use wcf\data\unfurl\url\image\UnfurlUrlImage;
use wcf\data\unfurl\url\image\UnfurlUrlImageList;

/**
 * Represents a collection of unfurled urls.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<UnfurlUrl>
 */
class UnfurlUrlCollection extends DatabaseObjectCollection
{
    /**
     * @var array<int, UnfurlUrlImage>
     */
    private array $images;

    public function getImage(UnfurlUrl $object): ?UnfurlUrlImage
    {
        $this->loadImages();

        return $this->images[$object->imageID] ?? null;
    }

    private function loadImages(): void
    {
        if (isset($this->images)) {
            return;
        }
        $this->images = [];

        $imageIDs = \array_unique(\array_map(
            fn($object) => $object->imageID,
            \array_filter(
                $this->getObjects(),
                fn($object) => $object->imageID !== null
            )
        ));
        if ($imageIDs === []) {
            return;
        }

        $list = new UnfurlUrlImageList();
        $list->setObjectIDs($imageIDs);
        $list->readObjects();
        $this->images = $list->getObjects();
    }
}
