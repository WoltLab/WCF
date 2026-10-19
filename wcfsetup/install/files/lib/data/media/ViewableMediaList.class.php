<?php

namespace wcf\data\media;

/**
 * Represents a list of viewable media files.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `MediaList` instead.
 *
 * @method  ViewableMedia       current()
 * @method  ViewableMedia[]     getObjects()
 * @method  ViewableMedia|null  getSingleObject()
 * @method  ViewableMedia|null  search($objectID)
 * @property    ViewableMedia[] $objects
 */
class ViewableMediaList extends MediaList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = ViewableMedia::class;

    public function __construct(
        private readonly ?int $languageID = null
    ) {
        parent::__construct();
    }

    #[\Override]
    public function readObjects()
    {
        parent::readObjects();

        if ($this->languageID !== null) {
            foreach ($this->objects as $mediaID => $media) {
                $this->objects[$mediaID] = $media->getLocalizedVersion($this->languageID);
            }
        }
    }
}
