<?php

namespace wcf\event\listView\user;

use wcf\event\IPsr14Event;
use wcf\system\listView\user\PollManagerListView;

/**
 * Indicates that the list view of the poll manager has been initialized.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PollManagerListViewInitialized implements IPsr14Event
{
    public function __construct(public readonly PollManagerListView $listView) {}
}
