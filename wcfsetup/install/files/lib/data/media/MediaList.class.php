<?php

namespace wcf\data\media;

use wcf\data\DatabaseObjectList;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Represents a list of media files.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method  Media       current()
 * @method  Media[]     getObjects()
 * @method  Media|null  getSingleObject()
 * @method  Media|null  search($objectID)
 * @property    Media[] $objects
 */
class MediaList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Media::class;

    /**
     * Adds conditions to search the media files by a certain search string.
     *
     * @param string $searchString
     */
    public function addSearchConditions($searchString)
    {
        if ($searchString === '') {
            return;
        }

        $searchString = '%' . \addcslashes($searchString, '_%') . '%';

        $this->sqlConditionJoins .= '
            LEFT JOIN   wcf1_media_content media_content
            ON          media_content.mediaID = media.mediaID';

        $conditionBuilder = new PreparedStatementConditionBuilder(false, 'OR');
        $conditionBuilder->add('media_content.title LIKE ?', [$searchString]);
        $conditionBuilder->add('media_content.caption LIKE ?', [$searchString]);
        $conditionBuilder->add('media_content.altText LIKE ?', [$searchString]);
        $conditionBuilder->add('media.filename LIKE ?', [$searchString]);
        $this->getConditionBuilder()->add('(' . $conditionBuilder->__toString() . ')', $conditionBuilder->getParameters());
    }
}
