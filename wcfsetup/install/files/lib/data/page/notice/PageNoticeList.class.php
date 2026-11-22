<?php

namespace wcf\data\page\notice;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of page notices.
 *
 * @author      Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectList<PageNotice>
 */
final class PageNoticeList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'page_notice.time DESC';
}
