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
 *
 * @extends DatabaseObjectList<Media>
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
     * @return void
     */
    public function addSearchConditions(string $searchString)
    {
        if ($searchString === '') {
            return;
        }

        $searchString = '%' . \addcslashes($searchString, '_%') . '%';

        $conditionBuilder = new PreparedStatementConditionBuilder(false, 'OR');
        $conditionBuilder->add(
            'media.mediaID IN (
                SELECT  mediaID
                FROM    wcf1_media_l10n
                WHERE   title LIKE ?
                    OR  caption LIKE ?
                    OR  altText LIKE ?
            )',
            [$searchString, $searchString, $searchString]
        );
        $conditionBuilder->add('media.filename LIKE ?', [$searchString]);
        $this->getConditionBuilder()->add('(' . $conditionBuilder->__toString() . ')', $conditionBuilder->getParameters());
    }
}
