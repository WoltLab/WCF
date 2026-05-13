<?php

namespace wcf\event\listView\user;

use wcf\event\IPsr14Event;
use wcf\system\listView\user\UserBirthdayListView;

/**
 * Indicates that the birthday list view has been initialized.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserBirthdayListViewInitialized implements IPsr14Event
{
    public function __construct(public readonly UserBirthdayListView $listView) {}
}
