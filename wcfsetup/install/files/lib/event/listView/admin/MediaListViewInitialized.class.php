<?php

namespace wcf\event\listView\admin;

use wcf\event\IPsr14Event;
use wcf\system\listView\admin\MediaListView;

/**
 * Indicates that the media list view has been initialized.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MediaListViewInitialized implements IPsr14Event
{
    public function __construct(public readonly MediaListView $listView) {}
}
