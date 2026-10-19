<?php

namespace wcf\data\media;

use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a viewable media file.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `Media` instead.
 *
 * @mixin   Media
 * @extends DatabaseObjectDecorator<Media>
 */
class ViewableMedia extends DatabaseObjectDecorator implements \Stringable
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Media::class;

    /**
     * @param string[] $content
     * @return void
     * @deprecated 6.3 The localized content is loaded by `Media` itself.
     */
    public function setLocalizedContent(int $languageID, array $content)
    {
        // does nothing
    }

    /**
     * Returns an instance of this class with localized versions.
     *
     * @return ViewableMedia
     */
    public function getLocalizedVersion(int $languageID)
    {
        return new self($this->getDecoratedObject()->getLocalizedVersion($languageID));
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getDecoratedObject()->getTitle();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getDecoratedObject()->__toString();
    }

    /**
     * Returns the viewable media file with the given id.
     *
     * @return  ViewableMedia|null
     */
    public static function getMedia(int $mediaID)
    {
        $mediaList = new ViewableMediaList();
        $mediaList->setObjectIDs([$mediaID]);
        $mediaList->readObjects();

        return $mediaList->search($mediaID);
    }
}
