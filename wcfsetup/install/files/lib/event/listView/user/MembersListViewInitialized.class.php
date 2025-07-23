<?php

namespace wcf\event\listView\user;

use wcf\event\IPsr14Event;
use wcf\system\listView\user\MembersListView;

/**
 * Indicates that the members list view has been initialized.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class MembersListViewInitialized implements IPsr14Event
{
    public function __construct(public readonly MembersListView $listView) {}
}
