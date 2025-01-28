<?php

namespace wcf\event\gridView\admin;

use wcf\event\IPsr14Event;
use wcf\system\gridView\admin\CronjobLogGridView;

/**
 * Indicates that the cronjob log grid view has been initialized.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class CronjobLogGridViewInitialized implements IPsr14Event
{
    public function __construct(public readonly CronjobLogGridView $gridView) {}
}
