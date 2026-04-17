<?php

namespace wcf\data\media;

use wcf\system\WCF;

/**
 * Represents a list of viewable media files.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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

    public function __construct(?int $languageID = null)
    {
        parent::__construct();

        if ($languageID === null) {
            $languageID = WCF::getLanguage()->languageID;
        }

        // fetch content data
        $this->sqlSelects .= "media_content.*, COALESCE(media.languageID, " . $languageID . ") AS localizedLanguageID";
        $this->sqlJoins .= "
            LEFT JOIN   wcf1_media_content media_content
            ON          media_content.mediaID = media.mediaID
                    AND media_content.languageID = COALESCE(media.languageID, " . $languageID . ")";
    }
}
