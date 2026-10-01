<?php

namespace wcf\data\box\content;

use wcf\data\DatabaseObjectList;
use wcf\data\media\ViewableMediaList;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Represents a list of box content.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<BoxContent>
 */
class BoxContentList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = BoxContent::class;

    /**
     * enables/disables the loading of box content images
     * @var bool
     */
    protected $imageLoading = false;

    /**
     * enables/disables the loading of embedded objects
     * @var bool
     */
    protected $embeddedObjectLoading = false;

    #[\Override]
    public function readObjects()
    {
        parent::readObjects();

        if ($this->imageLoading) {
            self::loadImages($this->getObjects());
        }

        if ($this->embeddedObjectLoading) {
            self::loadEmbeddedObjects($this->getObjects());
        }
    }

    /**
     * Loads the images of the given box contents.
     *
     * @param BoxContent[] $boxContents
     * @since 6.3
     */
    public static function loadImages(array $boxContents): void
    {
        $imageIDs = [];
        foreach ($boxContents as $boxContent) {
            if ($boxContent->imageID !== null) {
                $imageIDs[] = $boxContent->imageID;
            }
        }

        if ($imageIDs === []) {
            return;
        }

        $mediaList = new ViewableMediaList();
        $mediaList->setObjectIDs($imageIDs);
        $mediaList->readObjects();
        $images = $mediaList->getObjects();

        foreach ($boxContents as $boxContent) {
            if ($boxContent->imageID !== null && isset($images[$boxContent->imageID])) {
                $boxContent->setImage($images[$boxContent->imageID]);
            }
        }
    }

    /**
     * Loads the embedded objects of the given box contents.
     *
     * @param BoxContent[] $boxContents
     * @since 6.3
     */
    public static function loadEmbeddedObjects(array $boxContents): void
    {
        $embeddedObjectBoxContentIDs = [];
        foreach ($boxContents as $boxContent) {
            if ($boxContent->hasEmbeddedObjects !== 0) {
                $embeddedObjectBoxContentIDs[] = $boxContent->boxContentID;
            }
        }

        if ($embeddedObjectBoxContentIDs !== []) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects(
                'com.woltlab.wcf.box.content',
                $embeddedObjectBoxContentIDs
            );
        }
    }

    /**
     * Enables/disables the loading of box content images.
     *
     * @return void
     */
    public function enableImageLoading(bool $enable = true)
    {
        $this->imageLoading = $enable;
    }

    /**
     * Enables/disables the loading of embedded objects.
     *
     * @return void
     */
    public function enableEmbeddedObjectLoading(bool $enable = true)
    {
        $this->embeddedObjectLoading = $enable;
    }
}
